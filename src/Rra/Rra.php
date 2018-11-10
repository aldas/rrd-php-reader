<?php
declare(strict_types=1);

namespace RrdPhpReader\Rra;


use RrdPhpReader\Exception\RrdRangeException;
use RrdPhpReader\RrdData;

class Rra
{
    /** @var RrdData */
    private $rrdData;

    /** @var RraInfo */
    private $rraInfo;

    private $dataSourceCount;
    private $baseOffsetIndex;
    private $cur_row;
    private $rowCount;
    private $row_size;


    public function __construct(RrdData $rrdData, $rra_ptr_idx, RraInfo $rra_info, $dataSourceCount, int $rraBaseOffsetIndex)
    {
        $this->rrdData = $rrdData;
        $this->rraInfo = $rra_info;
        $this->dataSourceCount = $dataSourceCount;
        $this->row_size = $dataSourceCount * 8;
        $this->rowCount = $rra_info->getRowCount();


        $this->baseOffsetIndex = $rraBaseOffsetIndex;

        // get imediately, since it will be needed often
        $this->cur_row = $rrdData->getLongAt($rra_ptr_idx);
    }

    private function calculateIndex(int $row_idx, int $ds_idx)
    {
        if (($row_idx >= 0) && ($row_idx < $this->rowCount)) {
            if (($ds_idx >= 0) && ($ds_idx < $this->dataSourceCount)) {
                // it is round robin, starting from cur_row+1
                $real_row_idx = $row_idx + $this->cur_row + 1;
                if ($real_row_idx >= $this->rowCount) {
                    $real_row_idx -= $this->rowCount;
                }
                return $this->row_size * $real_row_idx + $ds_idx * 8;
            }
            throw new RrdRangeException("DS idx ({$row_idx}) out of range [0-{$this->dataSourceCount}).");
        }
        throw new RrdRangeException("Row idx ({$row_idx}) out of range [0-{$this->rowCount}).");
    }


    public function getIdx(): int
    {
        return $this->rraInfo->getIdx();
    }

    public function getRowCount(): int
    {
        return $this->rraInfo->getRowCount();
    }

    public function getDsCount(): int
    {
        return $this->dataSourceCount;
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
        return $this->rrdData->getDoubleAt($this->baseOffsetIndex + $this->calculateIndex($row_idx, $ds_idx));
    }

    public function __destruct()
    {
        $this->rrdData = null;
    }

    public function __toString()
    {
        return $this->rraInfo->__toString();
    }

    public function getRraInfo(): RraInfo
    {
        return $this->rraInfo;
    }
}