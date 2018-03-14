<?php
declare(strict_types=1);

namespace RrdPhpReader;

class RrdDs
{
    /**
     * @var RrdData
     */
    private $rrdData;

    /**
     * @var int
     */
    private $idx;

    /**
     * @var int
     */
    private $myIdx;

    public function __construct(RrdData $rrdData, int $idx, int $myIdx)
    {
        $this->rrdData = $rrdData;
        $this->idx = $idx;
        $this->myIdx = $myIdx;
    }

    public function getIdx(): int
    {
        return $this->myIdx;
    }

    public function getName(): string
    {
        return $this->rrdData->getCStringAt($this->idx, 20);
    }

    public function getType(): string
    {
        return $this->rrdData->getCStringAt($this->idx + 20, 20);
    }

    public function getMin(): float
    {
        return $this->rrdData->getDoubleAt($this->idx + 48);
    }

    public function getMax(): float
    {
        return $this->rrdData->getDoubleAt($this->idx + 56);
    }
}