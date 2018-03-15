<?php
declare(strict_types=1);

namespace RrdPhpReader\Rra;


use RrdPhpReader\Exception\RrdRangeException;
use RrdPhpReader\RrdData;

class Rra
{
    /**
     * @var RrdData
     */
    private $rrdData;

    /**
     * @var RraInfo
     */
    private $rraInfo;

    private $rra_ptr_idx;
    private $header_size;
    private $prev_row_cnts;
    private $ds_cnt;
    private $base_rrd_db_idx;
    private $cur_row;
    private $row_cnt;
    private $row_size;


    public function __construct(RrdData $rrdData, $rra_ptr_idx, RraInfo $rra_info, $header_size, $prev_row_cnts, $ds_cnt)
    {

        $this->rrdData = $rrdData;
        $this->rra_ptr_idx = $rra_ptr_idx;
        $this->rraInfo = $rra_info;
        $this->header_size = $header_size;
        $this->prev_row_cnts = $prev_row_cnts;
        $this->ds_cnt = $ds_cnt;


        $this->row_cnt = $rra_info->getNrRows();

        $this->row_size = $ds_cnt * 8;

        $this->base_rrd_db_idx = $header_size + $prev_row_cnts * $this->row_size;

        // get imediately, since it will be needed often
        $this->cur_row = $rrdData->getLongAt($rra_ptr_idx);
    }

    private function calc_idx(int $row_idx, int $ds_idx)
    {
        if (($row_idx >= 0) && ($row_idx < $this->row_cnt)) {
            if (($ds_idx >= 0) && ($ds_idx < $this->ds_cnt)) {
                // it is round robin, starting from cur_row+1
                $real_row_idx = $row_idx + $this->cur_row + 1;
                if ($real_row_idx >= $this->row_cnt) {
                    $real_row_idx -= $this->row_cnt;
                }
                return $this->row_size * $real_row_idx + $ds_idx * 8;
            }
            throw new RrdRangeException("DS idx ({$row_idx}) out of range [0-{$this->ds_cnt}).");
        }
        throw new RrdRangeException("Row idx ({$row_idx}) out of range [0-{$this->row_cnt}).");
    }


    public function getIdx(): int
    {
        return $this->rraInfo->getIdx();
    }

    public function getRowCount(): int
    {
        return $this->row_cnt;
    }

    public function getDsCount(): int
    {
        return $this->ds_cnt;
    }

    public function getStep(): int
    {
        return $this->rraInfo->getStep();
    }

    public function getCFName(): string
    {
        return $this->rraInfo->getCFName();
    }

    public function getRow($row_idx, $ds_idx): float
    {
        return $this->rrdData->getDoubleAt($this->base_rrd_db_idx + $this->calc_idx($row_idx, $ds_idx));
    }

    public function __destruct()
    {
        $this->rrdData = null;
    }
}