<?php

namespace examples;

/**
 * Example class to generate test data on different platforms (linux vs windows vs 32bit vs 64bit)
 */
class ExampleRrdGenerator
{
    public static function createRrd($filename, int $timestamp)
    {
        if (file_exists($filename)) {
            echo 'exists' . PHP_EOL;
            return;
        }

        $start = $timestamp - 1;
        $step = 1;
        $creator = new \RRDCreator($filename, $start, $step);
        $creator->addDataSource('value:GAUGE:10:U:U');
        $creator->addDataSource('extra_value:GAUGE:10:U:U'); // to test multi DS scenario

        $rras = [
            // 1 second resolution for 4 seconds
            'AVERAGE:0.5:1:4',
            'MAX:0.5:1:4',
        ];

        foreach ($rras as $rra) {
            $creator->addArchive($rra);
        }

        $creator->save();
        echo 'createRrd done' . PHP_EOL;

    }

    /**
     * @throws \Exception
     */
    public static function fillRrd($filename, int $secondsCount, int $timestamp)
    {
        $updater = new \RRDUpdater($filename);

        echo 'fillRrd starting' . PHP_EOL;
        for ($i = 0; $i < $secondsCount; $i++) {
            $time = $timestamp + $i;
            $updater->update(
                [
                    'value' => $i,
                    'extra_value' => ($secondsCount - $i) / 2 // just to see double values
                ],
                $time
            );
        }
        echo 'fillRrd done' . PHP_EOL;
    }
}

$time = time();
$timestamp = 1521054885; // 2018-03-14T19:14:45+00:00
$filename = "generic-{$timestamp}-{$time}.rrd";
echo "Generating: {$filename}" . PHP_EOL;
ExampleRrdGenerator::createRrd($filename, $timestamp);
ExampleRrdGenerator::fillRrd($filename, 10, $timestamp);