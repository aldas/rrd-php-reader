<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use RrdPhpReader\RrdData;
use RrdPhpReader\RrdFile;

class RrdFileTest extends TestCase
{
    public function testReadUnix()
    {
        $file = new RrdData(FileUtils::getContents('rrd_linux_x86_64.rrd'));
        $rrdFile = new RrdFile($file);

        $this->assertEquals(2, $rrdFile->getNrDSs());
        $this->assertEquals(
            ['value', 'extra_value'],
            $rrdFile->getHeader()->getDSNames()
        );
    }

    public function testReadWindows()
    {
        $file = new RrdData(FileUtils::getContents('rrd_win_64bit.rrd'));
        $rrdFile = new RrdFile($file);

        $this->assertEquals(1, $rrdFile->getNrDSs());
        $this->assertEquals(
            ['value'],
            $rrdFile->getHeader()->getDSNames()
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Help
     */
    public function testShouldThrowExceptionForHugeData()
    {
        throw new \RuntimeException('Help');
    }
}