<?php

namespace examples;

/**
 * Example class to generate test data on different platforms (linux vs windows vs 32bit vs 64bit)
 */
class ExampleRrdGenerator
{
    public static function createRrd($filename)
    {
        if (file_exists($filename)) {
            echo 'exists' . PHP_EOL;
            return;
        }

        $start = time() - 1;
        $step = 1;
        $creator = new \RRDCreator($filename, $start, $step);
        $creator->addDataSource('value:GAUGE:10:U:U');
        $creator->addDataSource('extra_value:GAUGE:10:U:U'); // to test multi DS scenario

        $rras = [
            // 1 second resolution for 30 seconds
            'AVERAGE:0.5:1:30',
            'MIN:0.5:1:30',
            'MAX:0.5:1:30',
            // 5 second resolution for 2 minutes (120 seconds)
            'AVERAGE:0.5:5:120',
            'MIN:0.5:5:120',
            'MAX:0.5:5:120',
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
    public static function fillRrd($filename, int $secondsCount)
    {
        $updater = new \RRDUpdater($filename);

        echo 'fillRrd starting' . PHP_EOL;
        $time = time();
        for ($i = 0; $i < $secondsCount; $i++) {
            $updater->update(
                [
                    'value' => '' .$i,
                    'extra_value' => ''. (($secondsCount - $i) / 2) // just to see double values
                ],
                $time + $i
            );
        }
        echo 'fillRrd done' . PHP_EOL;
    }
}

$filename = 'generic-' . time() . '.rrd';
echo "Generating: {$filename}" . PHP_EOL;
ExampleRrdGenerator::createRrd($filename);
ExampleRrdGenerator::fillRrd($filename, 3600);