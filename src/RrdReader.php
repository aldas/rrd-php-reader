<?php
declare(strict_types=1);

namespace RrdPhpReader;


use InvalidArgumentException;
use RrdPhpReader\Exception\RrdException;
use RrdPhpReader\Rra\Rra;
use Traversable;

class RrdReader
{
    const OPTION_DS = 'ds';
    const OPTION_RRA_INDEX = 'rra_index';
    const OPTION_RRA_FILTER_CALLBACK = 'rra_filter_callback';
    const OPTION_ROW_FILTER_CALLBACK = 'row_filter_callback';

    /**  @var RrdFile */
    private $rrdFile;

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

    /**
     * @param array|null $options
     * @return Traversable
     */
    public function getAll(array $options = null): Traversable
    {
        $dataSources = $this->getDataSources($options);
        $rras = $this->getRras($options);

        return $this->extractRows($dataSources, $rras, $options);
    }

    /**
     * @return RrdRowValue[]
     */
    public function getAllAsArray(array $options = null): array
    {
        return iterator_to_array($this->getAll($options));
    }

    /**
     * @return RrdDs[]
     */
    public function getDataSources(array $options = null): array
    {
        $dataSources = [];
        $dsName = $options[static::OPTION_DS] ?? null;

        if ($dsName !== null) {
            $dataSources[$dsName] = $this->rrdFile->getDS($dsName);
        } else {
            $dataSources = $this->rrdFile->getAllDS();
        }

        return $dataSources;
    }

    /**
     * @return Rra[]
     */
    private function getRras(array $options = null): array
    {
        $rraIndex = $options[static::OPTION_RRA_INDEX] ?? null;
        if ($rraIndex !== null) {
            $rras[$rraIndex] = $this->rrdFile->getRRA((int)$rraIndex);
            return $rras;
        }

        $rras = $this->rrdFile->getAllRRAs();
        $rraFilterCallback = $options[static::OPTION_RRA_FILTER_CALLBACK] ?? null;
        if (!is_string($rraFilterCallback) && is_callable($rraFilterCallback)) {
            $tmpRras = [];
            foreach ($rras as $rra) {
                if ($rraFilterCallback($rra)) {
                    $tmpRras[] = $rra;
                }

            }
            $rras = $tmpRras;
        }
        return $rras;
    }

    /**
     * Iterator returning data by timestamp => RrdRowValue pairs
     *
     * @param RrdDs[] $dataSources
     * @param Rra[] $rras
     * @param array|null $options
     *
     * @return Traversable
     */
    private function extractRows(array $dataSources, array $rras, array $options = null)
    {
        $rowCallback = $options[static::OPTION_ROW_FILTER_CALLBACK] ?? null;
        $hasRowCallback = !is_string($rowCallback) && is_callable($rowCallback);

        $time = $this->rrdFile->getLastUpdate();
        foreach ($rras as $index => $rra) {
            $rowCount = $rra->getRowCount();
            $step = $rra->getStep();
            $startTimestamp = $time - ($step * $rowCount);
            $currentTimestamp = $startTimestamp;
            for ($row = 0; $row < $rowCount; $row++) {
                $currentTimestamp += $step;
                foreach ($dataSources as $dsName => $dsTmp) {
                    $rowValue = $rra->getRow($row, $dsTmp->getIndex());
                    if ($hasRowCallback && !$rowCallback($currentTimestamp, $rowValue, $dsTmp, $rra->getRraInfo())) {
                        continue;
                    }
                    yield new RrdRowValue($currentTimestamp, $rowValue, $dsName, $rra->getRraInfo());
                }
            }
        }
    }

    public function outputAsCsv($handle, array $options = null)
    {
        if (!is_resource($handle)) {
            throw new InvalidArgumentException('resource expected, ' . gettype($handle) . ' given');
        }
        $delimiter = $options['delimiter'] ?? ',';

        fputcsv($handle, RrdRowValue::asHeader(), $delimiter);

        $it = $this->getAll($options);
        /** @var RrdRowValue $value */
        foreach ($it as $value) {
            fputcsv($handle, $value->asArray(), $delimiter);
        }

    }
}