<?php

namespace Tests;


use PHPUnit\Framework\TestCase;
use RrdPhpReader\RrdReader;

class RrdReaderTest extends TestCase
{
    const EXAMPLE_FOR_VALUE = [
        1521054891 => 6.0,
        1521054892 => 7.0,
        1521054893 => 8.0,
        1521054894 => 9.0,
    ];

    const EXAMPLE_FOR_EXTRA_VALUE = [
        1521054891 => 2.0,
        1521054892 => 1.5,
        1521054893 => 1.0,
        1521054894 => 0.5,
    ];

    public function testReadDataForSpecificDsRra()
    {
        $reader = RrdReader::createFromString(FileUtils::getContents('rrd_linux_x86_64.rrd'));

        $data = $reader->setDs('value')->setRraIndex(0)->getAsArray();
        $this->assertEquals(
            self::EXAMPLE_FOR_VALUE,
            $data['value'][0]
        );
    }

    public function testReadDataAllRras()
    {
        $reader = RrdReader::createFromString(FileUtils::getContents('rrd_linux_x86_64.rrd'));

        $data = $reader->setDs('value')->getAsArray();

        $this->assertEquals(self::EXAMPLE_FOR_VALUE, $data['value'][0]);
        $this->assertEquals(self::EXAMPLE_FOR_VALUE, $data['value'][1]);
    }

    public function testReadDataAllDsAllRras()
    {
        $reader = RrdReader::createFromString(FileUtils::getContents('rrd_linux_x86_64.rrd'));

        $data = $reader->getAsArray();
        $this->assertEquals(self::EXAMPLE_FOR_VALUE, $data['value'][0]);
        $this->assertEquals(self::EXAMPLE_FOR_VALUE, $data['value'][1]);
        $this->assertEquals(self::EXAMPLE_FOR_EXTRA_VALUE, $data['extra_value'][0]);
        $this->assertEquals(self::EXAMPLE_FOR_EXTRA_VALUE, $data['extra_value'][1]);
    }

    public function testReadDataAllDsSpecificRra()
    {
        $reader = RrdReader::createFromString(FileUtils::getContents('rrd_linux_x86_64.rrd'));

        $data = $reader->setRraIndex(1)->getAsArray();

        $this->assertEquals(self::EXAMPLE_FOR_VALUE, $data['value'][1]);
        $this->assertEquals(self::EXAMPLE_FOR_EXTRA_VALUE, $data['extra_value'][1]);
    }
}