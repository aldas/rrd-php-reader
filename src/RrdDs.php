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
    private $dataIndex;

    /**
     * @var int
     */
    private $dsIndex;

    public function __construct(RrdData $rrdData, int $dataIndex, int $dsIndex)
    {
        $this->rrdData = $rrdData;
        $this->dataIndex = $dataIndex;
        $this->dsIndex = $dsIndex;
    }

    public function getIndex(): int
    {
        return $this->dsIndex;
    }

    public function getName(): string
    {
        return $this->rrdData->getCStringAt($this->dataIndex, 20);
    }

    public function getType(): string
    {
        return $this->rrdData->getCStringAt($this->dataIndex + 20, 20);
    }

    public function getMin(): float
    {
        return $this->rrdData->getDoubleAt($this->dataIndex + 48);
    }

    public function getMax(): float
    {
        return $this->rrdData->getDoubleAt($this->dataIndex + 56);
    }

    public function __destruct()
    {
        $this->rrdData = null;
    }
}