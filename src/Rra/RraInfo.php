<?php
declare(strict_types=1);

namespace RrdPhpReader\Rra;

class RraInfo
{
    private $rowCount;
    private $pdpStep;
    private $pdpPerRow;

    /**  @var string */
    private $cfName;

    public function __construct(string $cfName, $rowCount, $pdpStep, $pdpPerRow)
    {
        $this->rowCount = $rowCount;
        $this->pdpStep = $pdpStep;
        $this->pdpPerRow = $pdpPerRow;
        $this->cfName = $cfName;
    }

    /**
     * Get number of rows
     *
     * @return int
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * Get number of slots used for consolidation
     *
     * @return int
     */
    public function getPdpPerRow(): int
    {
        return $this->pdpPerRow;
    }

    /**
     * Get RRA step (expressed in seconds)
     *
     * @return int
     */
    public function getStep(): int
    {
        return $this->pdpStep * $this->getPdpPerRow();
    }

    /**
     * Get consolidation function name
     *
     * @return string
     */
    public function getCFName(): string
    {
        return $this->cfName;
    }

    public function __toString()
    {
        return sprintf(
            'cf=%s, step=%d, pdpPerRow=%d, rowCount=%d',
            $this->getCFName(),
            $this->getStep(),
            $this->getPdpPerRow(),
            $this->getRowCount()
        );
    }

}