<?php
declare(strict_types=1);

namespace RrdPhpReader;


use RrdPhpReader\Exception\InvalidRrdException;
use RrdPhpReader\Exception\RrdRangeException;
use RrdPhpReader\Rra\RraInfo;

//FIXME fix public variables
class RrdHeader
{
    /**
     * @var RrdData
     */
    private $rrdData;

    /**
     * @var string
     */
    private $rrd_version;

    /**
     * @var int
     */
    private $float_width;
    /**
     * @var int
     */
    private $float_align;

    /**
     * @var int
     */
    private $int_width;
    /**
     * @var int
     */
    private $int_align;

    /**
     * @var int
     */
    private $unival_width;

    /**
     * @var int
     */
    private $unival_align;

    /**
     * @var int
     */
    private $ds_cnt_idx;

    /**
     * @var int
     */
    private $rra_cnt_idx;

    /**
     * @var int
     */
    private $pdp_step_idx;

    /**
     * @var int
     */
    private $ds_cnt;

    /**
     * @var int
     */
    private $rra_cnt;

    /**
     * @var int
     */
    private $pdp_step;

    /**
     * @var int
     */
    private $top_header_size;

    /**
     * @var int
     */
    private $ds_def_idx;

    /**
     * @var int
     */
    private $ds_el_size;

    /**
     * @var int
     */
    private $rra_def_idx;

    /**
     * @var int
     */
    private $row_cnt_idx;

    /**
     * @var int
     */
    private $rra_def_el_size;

    /**
     * @var int
     */
    private $live_head_idx;

    /**
     * @var int
     */
    private $live_head_size;

    /**
     * @var int
     */
    private $pdp_prep_idx;

    /**
     * @var int
     */
    private $pdp_prep_el_size;

    /**
     * @var int
     */
    private $cdp_prep_idx;

    /**
     * @var int
     */
    private $cdp_prep_el_size;

    /**
     * @var int
     */
    public $rra_ptr_idx;

    /**
     * @var int
     */
    public $rra_ptr_el_size;

    /**
     * @var int
     */
    public $header_size;

    /**
     * @var int[]
     */
    private $rra_def_row_cnts;

    /**
     * @var int[]
     */
    public $rra_def_row_cnt_sums;


    /**
     * RrdHeader constructor.
     *
     * @param RrdData $rrdData
     * @throws \RrdPhpReader\Exception\InvalidRrdException
     */
    public function __construct(RrdData $rrdData)
    {
        $this->rrdData = $rrdData;

        $this->initialize();
        $this->calculateIndexes();
        $this->loadRowCounts();
    }

    /**
     * @throws InvalidRrdException
     */
    private function initialize()
    {
        $length = $this->rrdData->getLength();
        if ($length < 1) {
            throw new InvalidRrdException('Empty file.');
        }
        if ($length < 16) {
            throw new InvalidRrdException('File too short.');
        }
        $magicId = $this->rrdData->getCStringAt(0, 4);
        if ($magicId !== 'RRD') {
            throw new InvalidRrdException('Wrong magic id.');
        }

        $this->rrd_version = $this->rrdData->getCStringAt(4, 5);
        if (!\in_array($this->rrd_version, ['0003', '0004', '0001'], true)) {
            throw new InvalidRrdException("Unsupported RRD version {$this->rrd_version}.");
        }

        $this->float_width = 8;
        if ($this->rrdData->getLongAt(12) === 0) {
            // not a double here... likely 64 bit
            $this->float_align = 8;
            if (!($this->rrdData->getDoubleAt(16) === 8.642135e+130)) { // TODO check what in PHP it would be
                // uhm... wrong endian?
                $this->rrdData->setSwitchEndian(true);
            }
            if ($this->rrdData->getDoubleAt(16) === 8.642135e+130) {// TODO check what in PHP it would be
                // now, is it all 64bit or only float 64 bit?
                if ($this->rrdData->getLongAt(28) === 0) {
                    // true 64 bit align
                    $this->int_align = 8;
                    $this->int_width = 8;
                } else {
                    // integers are 32bit aligned
                    $this->int_align = 4;
                    $this->int_width = 4;
                }
            } else {
                throw new InvalidRrdException('Magic float not found at 16.');
            }
        } else {
            /// should be 32 bit alignment
            if (!($this->rrdData->getDoubleAt(12) === 8.642135e+130)) {// TODO check what in PHP it would be
                // uhm... wrong endian?
                $this->rrdData->setSwitchEndian(true);
            }
            if ($this->rrdData->getDoubleAt(12) === 8.642135e+130) {// TODO check what in PHP it would be
                $this->float_align = 4;
                $this->int_align = 4;
                $this->int_width = 4;
            } else {
                throw new InvalidRrdException('Magic float not found at 12.');
            }
        }
        $this->unival_width = $this->float_width;
        $this->unival_align = $this->float_align;

        // process the header here, since I need it for validation

        // char magic[4], char version[5], double magic_float

        // long ds_cnt, long rra_cnt, long pdp_step, unival par[10]
        $this->ds_cnt_idx = (int)(ceil((4 + 5) / $this->float_align) * $this->float_align + $this->float_width);
        $this->rra_cnt_idx = $this->ds_cnt_idx + $this->int_width;
        $this->pdp_step_idx = $this->rra_cnt_idx + $this->int_width;

        //always get only the low 32 bits, the high 32 on 64 bit archs should always be 0
        $this->ds_cnt = $this->rrdData->getLongAt($this->ds_cnt_idx);
        if ($this->ds_cnt < 1) {
            throw new InvalidRrdException('ds count less than 1.');
        }

        $this->rra_cnt = $this->rrdData->getLongAt($this->rra_cnt_idx);
        if ($this->ds_cnt < 1) {
            throw new InvalidRrdException('rra count less than 1.');
        }

        $this->pdp_step = $this->rrdData->getLongAt($this->pdp_step_idx);
        if ($this->pdp_step < 1) {
            throw new InvalidRrdException('pdp step less than 1.');
        }

        // best guess, assuming no weird align problems
        $this->top_header_size = (int)(ceil(($this->pdp_step_idx + $this->int_width) / $this->unival_align) * $this->unival_align + 10 * $this->unival_width);
        $t = $this->rrdData->getLongAt($this->top_header_size);
        if ($t === 0) {
            throw new InvalidRrdException('Could not find first DS name.');
        }
    }

    private function calculateIndexes()
    {
        $this->ds_def_idx = $this->top_header_size;
        // char ds_nam[20], char dst[20], unival par[10]
        $this->ds_el_size = (int)(ceil((20 + 20) / $this->unival_align) * $this->unival_align + 10 * $this->unival_width);

        $this->rra_def_idx = $this->ds_def_idx + $this->ds_el_size * $this->ds_cnt;
        // char cf_nam[20], uint row_cnt, uint pdp_cnt, unival par[10]
        $this->row_cnt_idx = (int)(ceil(20 / $this->int_align) * $this->int_align);
        $this->rra_def_el_size = (int)(ceil(($this->row_cnt_idx + 2 * $this->int_width) / $this->unival_align) * $this->unival_align + 10 * $this->unival_width);

        $this->live_head_idx = $this->rra_def_idx + $this->rra_def_el_size * $this->rra_cnt;
        // time_t last_up, int last_up_usec
        $this->live_head_size = 2 * $this->int_width;

        $this->pdp_prep_idx = $this->live_head_idx + $this->live_head_size;
        // char last_ds[30], unival scratch[10]
        $this->pdp_prep_el_size = (int)(ceil(30 / $this->unival_align) * $this->unival_align + 10 * $this->unival_width);

        $this->cdp_prep_idx = $this->pdp_prep_idx + $this->pdp_prep_el_size * $this->ds_cnt;
        // unival scratch[10]
        $this->cdp_prep_el_size = 10 * $this->unival_width;

        $this->rra_ptr_idx = $this->cdp_prep_idx + $this->cdp_prep_el_size * $this->ds_cnt * $this->rra_cnt;
        // uint cur_row
        $this->rra_ptr_el_size = 1 * $this->int_width;

        $this->header_size = $this->rra_ptr_idx + $this->rra_ptr_el_size * $this->rra_cnt;
    }

    private function loadRowCounts()
    {
        $this->rra_def_row_cnts = [];
        $this->rra_def_row_cnt_sums = []; // how many rows before me

        for ($i = 0; $i < $this->rra_cnt; $i++) {
            $this->rra_def_row_cnts[$i] = $this->rrdData->getLongAt($this->rra_def_idx + $i * $this->rra_def_el_size + $this->row_cnt_idx);

            if ($i === 0) {
                $this->rra_def_row_cnt_sums[$i] = 0;
            } else {
                $this->rra_def_row_cnt_sums[$i] = $this->rra_def_row_cnt_sums[$i - 1] + $this->rra_def_row_cnts[$i - 1];
            }
        }
    }

    public function getMinStep(): int
    {
        return $this->pdp_step;
    }

    public function getLastUpdate(): int
    {
        return $this->rrdData->getLongAt($this->live_head_idx);
    }

    public function getNrDSs(): int
    {
        return $this->ds_cnt;
    }

    /**
     * @return string[]
     * @throws \RrdPhpReader\Exception\RrdRangeException
     */
    public function getDSNames(): array
    {
        $dsNames = [];
        for ($idx = 0; $idx < $this->ds_cnt; $idx++) {
            $dsNames[] = $this->getDSbyIdx($idx)->getName();
        }

        return $dsNames;
    }

    /**
     * @param int $idx
     * @return RrdDs
     * @throws \RrdPhpReader\Exception\RrdRangeException
     */
    public function getDSbyIdx(int $idx): RrdDs
    {
        if (($idx >= 0) && ($idx < $this->ds_cnt)) {
            return new RrdDs(
                $this->rrdData,
                $this->ds_def_idx + $this->ds_el_size * $idx,
                $idx
            );
        }
        throw new RrdRangeException("DS idx ({$idx}) out of range [0-{$this->ds_cnt}).");
    }

    /**
     * @param string $name
     * @return RrdDs
     * @throws \RrdPhpReader\Exception\RrdRangeException
     */
    public function getDSbyName(string $name): RrdDs
    {
        for ($idx = 0; $idx < $this->ds_cnt; $idx++) {
            $ds = $this->getDSbyIdx($idx);
            $ds_name = $ds->getName();
            if ($ds_name === $name) {
                return $ds;
            }
        }
        throw new RrdRangeException("DS name {$name} unknown.");
    }

    public function getNrRRAs(): int
    {
        return $this->rra_cnt;
    }

    /**
     * @param $idx
     * @return RraInfo
     * @throws \RrdPhpReader\Exception\RrdRangeException
     */
    public function getRRAInfo(int $idx): RraInfo
    {
        if (($idx >= 0) && ($idx < $this->rra_cnt)) {
            $rra_def_idx = $this->rra_def_idx + $idx * $this->rra_def_el_size;
            return new RraInfo(
                $this->rrdData,
                $rra_def_idx,
                $this->int_align,
                $this->rra_def_row_cnts[$idx],
                $this->pdp_step,
                $idx
            );
        }
        throw new RrdRangeException("RRA idx ({$idx}) out of range [0-{$this->rra_cnt}).");
    }

}