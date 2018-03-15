<?php
declare(strict_types=1);

namespace RrdPhpReader;


use RrdPhpReader\Exception\RrdException;
use RrdPhpReader\Rra\Rra;

class RrdReader
{
    /**
     * @var RrdFile
     */
    private $rrdFile = null;

    /**
     * @var string
     */
    private $ds = null;

    /**
     * @var int
     */
    private $rraIndex;

    protected function __construct(RrdFile $rrdFile)
    {
        $this->rrdFile = $rrdFile;
    }

    public function getRrd(): RrdFile
    {
        return $this->rrdFile;
    }

    public static function createFromPath(string $path)
    {
        if (!is_file($path)) {
            throw new RrdException('Path does not exist or is not a file');
        }
        return new static(new RrdFile(new RrdData(file_get_contents($path))));
    }

    public static function createFromString(string $contents)
    {
        return new static(new RrdFile(new RrdData($contents)));
    }

    public static function create(RrdData $data)
    {
        return new static(new RrdFile($data));
    }

    public function getAsArray(): array
    {
        /** @var RrdDs[] $ds */
        $ds = [];
        if ($this->ds !== null) {
            $ds[$this->ds] = $this->rrdFile->getDS($this->ds);
        } else {
            for ($i = 0; $i < $this->rrdFile->getNrDSs(); $i++) {
                $rrdDs = $this->rrdFile->getDS($i);
                $ds[$rrdDs->getName()] = $rrdDs;
            }
        }

        /** @var Rra[] $rras */
        $rras = [];
        if ($this->rraIndex !== null) {
            $rras[$this->rraIndex] = $this->rrdFile->getRRA($this->rraIndex);
        } else {
            for ($i = 0; $i < $this->rrdFile->getNrRRAs(); $i++) {
                $rras[$i] = $this->rrdFile->getRRA($i);
            }
        }

        $data = [];

        $time = $this->rrdFile->getLastUpdate();
        foreach ($rras as $rraIndex => $rra) {
            $rowCount = $rra->getRowCount();
            $step = $rra->getStep();
            $startTimestamp = $time - ($step * $rowCount);
            $currentTimestamp = $startTimestamp;
            for ($row = 0; $row < $rowCount; $row++) {
                $currentTimestamp += $step;
                foreach ($ds as $dsName => $dsTmp) {
                    $data[$dsName][$rraIndex][$currentTimestamp] = $rra->getRow($row, $dsTmp->getIndex());
                }
            }
        }

        return $data;
    }

    public function setDs(string $ds): RrdReader
    {
        $this->ds = $ds;
        return $this;
    }

    public function setRraIndex(int $rraIndex): RrdReader
    {
        $this->rraIndex = $rraIndex;
        return $this;
    }
}