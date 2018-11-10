<?php
declare(strict_types=1);

namespace RrdPhpReader;

class RrdDs
{
    /** @var int */
    private $dsIndex;

    /**  @var string */
    private $name;

    /** @var string */
    private $type;

    /** @var float */
    private $min;

    /** @var float */
    private $max;

    public function __construct(int $dsIndex, string $name, string $type, float $min, float $max)
    {
        $this->dsIndex = $dsIndex;
        $this->name = $name;
        $this->type = $type;
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMin(): float
    {
        return $this->min;
    }

    public function getMax(): float
    {
        return $this->max;
    }

    public function getIndex(): int
    {
        return $this->dsIndex;
    }

    public function __toString()
    {
        return sprintf(
            'name=%s, type=%s, min=%f, max=%f',
            $this->getName(),
            $this->getType(),
            $this->getMin(),
            $this->getMax()
        );
    }

    public static function fromData(RrdData $rrdData, int $dataIndex, int $dsIndex): RrdDs
    {
        $name = $rrdData->getCStringAt($dataIndex, 20);
        $type = $rrdData->getCStringAt($dataIndex + 20, 20);
        $min = $rrdData->getDoubleAt($dataIndex + 48);
        $max = $rrdData->getDoubleAt($dataIndex + 56);

        return new self(
            $dsIndex,
            $name,
            $type,
            $min,
            $max
        );
    }
}