<?php
declare(strict_types=1);

namespace RrdPhpReader;

use RrdPhpReader\Rra\Rra;
use RrdPhpReader\Rra\RraInfo;

class RrdFile
{
    /**
     * @var RrdHeader
     */
    private $header;

    /**
     * @var RrdData
     */
    private $rrdData;

    public function __construct(RrdData $rrdData)
    {
        // load file contents?
        // create header;
        $this->rrdData = $rrdData;
        $this->header = new RrdHeader($rrdData);
    }

    public function getHeader(): RrdHeader
    {
        return $this->header;
    }

    public function getMinStep()
    {
        return $this->header->getMinStep();
    }

    public function getLastUpdate()
    {
        return $this->header->getLastUpdate();
    }

    public function getNrDSs()
    {
        return $this->header->getNrDSs();
    }

    public function getDS($ds)
    {
        if (\is_string($ds)) {
            return $this->header->getDSbyName($ds);
        }
        return $this->header->getDSbyIdx($ds);
    }

    public function getNrRRAs()
    {
        return $this->header->getNrRRAs();
    }

    public function getRRAInfo(int $idx): RraInfo
    {
        return $this->header->getRRAInfo($idx);
    }

    public function getRRA(int $idx): Rra
    {
        $rra_info = $this->header->getRRAInfo($idx);
        return new Rra(
            $this->rrdData,
            $this->header->rra_ptr_idx + $idx * $this->header->rra_ptr_el_size,
            $rra_info,
            $this->header->header_size,
            $this->header->rra_def_row_cnt_sums[$idx],
            $this->header->getNrDSs()
        );
    }
}