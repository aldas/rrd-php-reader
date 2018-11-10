<?php

namespace Tests;


use PHPUnit\Framework\TestCase;
use RrdPhpReader\Rra\RraInfo;
use RrdPhpReader\RrdReader;
use RrdPhpReader\RrdRowValue;

class RrdReaderTest extends TestCase
{
    private static $EXAMPLE_FOR_VALUE;
    private static $EXAMPLE_FOR_VALUE_MAX;
    private static $EXAMPLE_FOR_EXTRA_VALUE;
    private static $EXAMPLE_FOR_EXTRA_VALUE_MAX;

    public static function setUpBeforeClass()
    {
        $rraAverage = new RraInfo('AVERAGE', 4, 1, 1);
        $rraMax = new RraInfo('MAX', 4, 1, 1);

        self::$EXAMPLE_FOR_VALUE = [
            new RrdRowValue(1521054891, 6.0, 'value', $rraAverage),
            new RrdRowValue(1521054892, 7.0, 'value', $rraAverage),
            new RrdRowValue(1521054893, 8.0, 'value', $rraAverage),
            new RrdRowValue(1521054894, 9.0, 'value', $rraAverage),
        ];
        self::$EXAMPLE_FOR_VALUE_MAX = [
            new RrdRowValue(1521054891, 6.0, 'value', $rraMax),
            new RrdRowValue(1521054892, 7.0, 'value', $rraMax),
            new RrdRowValue(1521054893, 8.0, 'value', $rraMax),
            new RrdRowValue(1521054894, 9.0, 'value', $rraMax),
        ];
        self::$EXAMPLE_FOR_EXTRA_VALUE = [
            new RrdRowValue(1521054891, 2.0, 'extra_value', $rraAverage),
            new RrdRowValue(1521054892, 1.5, 'extra_value', $rraAverage),
            new RrdRowValue(1521054893, 1.0, 'extra_value', $rraAverage),
            new RrdRowValue(1521054894, 0.5, 'extra_value', $rraAverage),
        ];
        self::$EXAMPLE_FOR_EXTRA_VALUE_MAX = [
            new RrdRowValue(1521054891, 2.0, 'extra_value', $rraMax),
            new RrdRowValue(1521054892, 1.5, 'extra_value', $rraMax),
            new RrdRowValue(1521054893, 1.0, 'extra_value', $rraMax),
            new RrdRowValue(1521054894, 0.5, 'extra_value', $rraMax),
        ];
    }

    public function allArchFiles(): array
    {
        return [
            'rrd_linux_x86_64' => ['rrd_linux_x86_64.rrd'],
            'rrd_windows_x86_64' => ['rrd_windows_x86_64.rrd'],
            'rrd_windows_server_x86_64' => ['rrd_windows_server_x86_64.rrd'],
        ];
    }

    /**
     * @dataProvider allArchFiles
     */
    public function testReadDataForSpecificDsRra(string $testRrd)
    {
        $reader = RrdReader::createFromString(FileUtils::getContents($testRrd));

        $data = $reader->getAllAsArray(['ds' => 'value', 'rra_index' => 0]);
        $this->assertEquals(
            self::$EXAMPLE_FOR_VALUE,
            $data
        );
    }

    /**
     * @dataProvider allArchFiles
     */
    public function testReadDataAllRras(string $testRrd)
    {
        $reader = RrdReader::createFromString(FileUtils::getContents($testRrd));

        $data = $reader->getAllAsArray(['ds' => 'value']);

        $this->assertEquals(self::$EXAMPLE_FOR_VALUE[0], $data[0]);
        $this->assertEquals(self::$EXAMPLE_FOR_VALUE[3], $data[3]);
        $this->assertEquals(self::$EXAMPLE_FOR_VALUE_MAX[0], $data[4]);
        $this->assertEquals(self::$EXAMPLE_FOR_VALUE_MAX[3], $data[7]);
    }

    /**
     * @dataProvider allArchFiles
     */
    public function testReadDataAllDsAllRras(string $testRrd)
    {
        $reader = RrdReader::createFromString(FileUtils::getContents($testRrd));

        $data = $reader->getAllAsArray();

        $this->assertEquals(self::$EXAMPLE_FOR_VALUE[0], $data[0]);
        $this->assertEquals(self::$EXAMPLE_FOR_EXTRA_VALUE[0], $data[1]);

        $this->assertEquals(self::$EXAMPLE_FOR_VALUE[3], $data[6]);
        $this->assertEquals(self::$EXAMPLE_FOR_EXTRA_VALUE[3], $data[7]);

        $this->assertEquals(self::$EXAMPLE_FOR_VALUE_MAX[0], $data[8]);
        $this->assertEquals(self::$EXAMPLE_FOR_EXTRA_VALUE_MAX[0], $data[9]);

        $this->assertEquals(self::$EXAMPLE_FOR_VALUE_MAX[3], $data[14]);
        $this->assertEquals(self::$EXAMPLE_FOR_EXTRA_VALUE_MAX[3], $data[15]);

    }

    /**
     * @dataProvider allArchFiles
     */
    public function testReadDataAllDsSpecificRra(string $testRrd)
    {
        $reader = RrdReader::createFromString(FileUtils::getContents($testRrd));

        $data = $reader->getAllAsArray(['ds' => 'value', 'rra_index' => 1]);

        $this->assertEquals(self::$EXAMPLE_FOR_VALUE_MAX[0], $data[0]);
        $this->assertEquals(self::$EXAMPLE_FOR_VALUE_MAX[3], $data[3]);
    }
}