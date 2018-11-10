<?php

require __DIR__ . '/../vendor/autoload.php';

use RrdPhpReader\RrdReader;

if (php_sapi_name() !== 'cli') {
    echo 'Should be used only in command line interface';
    exit(1);
}

// usage: php rrd_to_csv.php --rrd="../tests/data/rrd_linux_x86_64.rrd" --csv=output.csv

$options = getopt('', [
    'rrd:',
    'csv:',
]);

$rrdPath = $options['rrd'] ?? __DIR__ . '/../tests/data/rrd_linux_x86_64.rrd';
$csvPath = $options['csv'] ?? __DIR__ . '/output.csv';

if (!file_exists($rrdPath)) {
    echo 'Rrd file does not exist!';
    exit(1);
}

$reader = RrdReader::createFromPath($rrdPath);

$fp = fopen($csvPath, 'wb');
$reader->outputAsCsv($fp, [
    'ds' => 'value'
]);
fclose($fp);

