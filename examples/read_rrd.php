<?php

require __DIR__ . '/../vendor/autoload.php';

use RrdPhpReader\Rra\RraInfo;
use RrdPhpReader\RrdDs;
use RrdPhpReader\RrdReader;
use RrdPhpReader\RrdRowValue;

if (php_sapi_name() !== 'cli') {
    echo 'Should be used only in command line interface';
    exit(1);
}

// usage: php read_rrd.php --rrd="../tests/data/rrd_linux_x86_64.rrd"

$options = getopt('', ['rrd:', 'csv:']);

$rrdPath = $options['rrd'] ?? __DIR__ . '/../tests/data/rrd_linux_x86_64.rrd';
if (!file_exists($rrdPath)) {
    echo 'Rrd file does not exist!';
    exit(1);
}

$reader = RrdReader::createFromPath($rrdPath);

$traversable = $reader->getAll([
    'ds' => 'value',
    'row_filter_callback' => function (int $timestamp, float $value, RrdDs $ds, RraInfo $rra) {
        return $value < 8;
    }
]);


/** @var RrdRowValue $value */
foreach ($traversable as $value) {
    echo $value . PHP_EOL;
}
