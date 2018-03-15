<?php
declare(strict_types=1);

namespace RrdPhpReader\Rra;


use RrdPhpReader\RrdData;

class RraInfo
{
    /**
     * @var RrdData
     */
    private $rrd_data;

    private $rra_def_idx;
    private $int_align;
    private $row_cnt;
    private $pdp_step;
    private $my_idx;
    private $rra_pdp_cnt_idx;

    public function __construct(RrdData $rrd_data, $rra_def_idx, $int_align, $row_cnt, $pdp_step, $my_idx)
    {
        $this->rrd_data = $rrd_data;
        $this->rra_def_idx = $rra_def_idx;
        $this->int_align = $int_align;
        $this->row_cnt = $row_cnt;
        $this->pdp_step = $pdp_step;
        $this->my_idx = $my_idx;

        $this->rra_pdp_cnt_idx = $rra_def_idx + (int)(ceil(20 / $int_align) * $int_align + $int_align);
    }

    public function getIdx(): int
    {
        return $this->my_idx;
    }

    /**
     * Get number of rows
     *
     * @return int
     */
    public function getNrRows(): int
    {
        return $this->row_cnt;
    }

    /**
     * Get number of slots used for consolidation
     *
     * @return int
     */
    public function getPdpPerRow(): int
    {
        return $this->rrd_data->getLongAt($this->rra_pdp_cnt_idx);
    }

    /**
     * Get RRA step (expressed in seconds)
     *
     * @return int
     */
    public function getStep(): int
    {
        return $this->pdp_step * $this->getPdpPerRow();
    }

    /**
     * Get consolidation function name
     *
     * @return string
     */
    public function getCFName(): string
    {
        return $this->rrd_data->getCStringAt($this->rra_def_idx, 20);
    }

}