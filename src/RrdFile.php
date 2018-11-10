<?php
declare(strict_types=1);

namespace RrdPhpReader;

use RrdPhpReader\Rra\Rra;
use RrdPhpReader\Rra\RraInfo;

class RrdFile
{
    /** @var RrdHeader */
    private $header;

    /** @var RrdData */
    private $rrdData;

    public function __construct(RrdData $rrdData)
    {
        $this->rrdData = $rrdData;
        $this->header = new RrdHeader($rrdData);
    }

    public function getHeader(): RrdHeader
    {
        return $this->header;
    }

    public function getStep(): int
    {
        return $this->header->getStep();
    }

    public function getLastUpdate(): int
    {
        return $this->header->getLastUpdate();
    }

    public function getDsCount(): int
    {
        return $this->header->getDsCount();
    }

    public function getDS($ds): RrdDs
    {
        if (\is_string($ds)) {
            return $this->header->getDSbyName($ds);
        }
        return $this->header->getDSbyIdx($ds);
    }

    /**
     * Return associative array (key=>ds) of all datasources in RRD file
     *
     * @return RrdDs[]
     */
    public function getAllDS(): array
    {
        $dataSources = [];
        for ($i = 0; $i < $this->getDsCount(); $i++) {
            $rrdDs = $this->getDS($i);
            $dataSources[$rrdDs->getName()] = $rrdDs;
        }
        return $dataSources;
    }

    public function getRraCount(): int
    {
        return $this->header->getRraCount();
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
            $this->header->rra_ptr_idx + ($idx * $this->header->rra_ptr_el_size),
            $rra_info,
            $this->header->getDsCount(),
            $this->header->getRraBaseOffsetIndex($idx)
        );
    }


    /**
     * Return all RRAs in RRD file
     *
     * @return Rra[]
     */
    public function getAllRRAs(): array
    {
        $rras = [];
        $rraCount = $this->getRraCount();
        for ($i = 0; $i < $rraCount; $i++) {
            $rras[$i] = $this->getRRA($i);
        }
        return $rras;
    }

    public function __destruct()
    {
        $this->header = null;
        $this->rrdData = null;
    }
}