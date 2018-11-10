<?php
declare(strict_types=1);

namespace RrdPhpReader;


use RrdPhpReader\Rra\RraInfo;

class RrdRowValue
{
    /** @var int timestamp */
    private $timestamp;

    /** @var float value */
    private $value;

    /** @var string datasource name */
    private $ds;

    /** @var RraInfo */
    private $rra;

    public function __construct(int $timestamp, float $value, string $ds, RraInfo $rra)
    {
        $this->timestamp = $timestamp;
        $this->value = $value;
        $this->ds = $ds;
        $this->rra = $rra;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getDs(): string
    {
        return $this->ds;
    }

    /**
     * consolidation function
     *
     * @return string
     */
    public function getCf(): string
    {
        return $this->rra->getCFName();
    }

    /**
     * @return RraInfo
     */
    public function getRra(): RraInfo
    {
        return $this->rra;
    }

    public function __toString()
    {
        return sprintf(
            'timestamp=%d, value=%f, cf=%s, ds=%s, step=%d',
            $this->timestamp,
            $this->value,
            $this->rra->getCFName(),
            $this->ds,
            $this->rra->getStep()
        );
    }

    public function asArray(): array
    {
        return [
            'timestamp' => $this->timestamp,
            'value' => $this->value,
            'cf' => $this->rra->getCFName(),
            'ds' => $this->ds,
            'step' => $this->rra->getStep()
        ];
    }

    public static function asHeader(): array
    {
        return [
            'timestamp',
            'value',
            'cf',
            'ds',
            'step',
        ];
    }
}