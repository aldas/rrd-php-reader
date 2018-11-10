<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use RrdPhpReader\RrdData;
use RrdPhpReader\RrdFile;

class RrdFileTest extends TestCase
{
    protected function checkFile($filename)
    {
        $rrdFile = new RrdFile(new RrdData(FileUtils::getContents($filename)));

        $this->assertEquals(1521054894, $rrdFile->getLastUpdate()); // initial value from 1521054885 + (10-1)
        $this->assertEquals(2, $rrdFile->getDsCount());
        $this->assertEquals(
            ['value', 'extra_value'],
            $rrdFile->getHeader()->getDSNames()
        );

        $extraValueDs = $rrdFile->getDS('extra_value');
        $this->assertEquals('GAUGE', $extraValueDs->getType());

        $rras = $this->getRraData($rrdFile, $extraValueDs->getIndex());
        $this->assertEquals([2, 1.5, 1, 0.5], $rras[0]['data']);
        $this->assertEquals([2, 1.5, 1, 0.5], $rras[1]['data']);

        $valueDs = $rrdFile->getDS('value');
        $this->assertEquals('GAUGE', $valueDs->getType());

        $rras2 = $this->getRraData($rrdFile, $valueDs->getIndex());
        $this->assertEquals([6.0, 7.0, 8.0, 9.0], $rras2[0]['data']);
        $this->assertEquals([6.0, 7.0, 8.0, 9.0], $rras2[1]['data']);
    }

    public function testReadUnix()
    {
        $this->checkFile('rrd_linux_x86_64.rrd');
    }

    public function testReadWindows()
    {
        $this->checkFile('rrd_windows_x86_64.rrd');
    }

    public function testReadWindowsServer()
    {
        $this->checkFile('rrd_windows_server_x86_64.rrd');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Help
     */
    public function testShouldThrowExceptionForHugeData()
    {
        throw new \RuntimeException('Help');
    }

    private function getRraData(RrdFile $rrdFile, int $dsIndex): array
    {
        $rras = [];
        for ($i = 0; $i < $rrdFile->getRraCount(); $i++) {
            $rra = $rrdFile->getRRA($i);

            $data = [];
            $rowCount = $rra->getRowCount();
            for ($row = 0; $row < $rowCount; $row++) {
                $data[] = $rra->getRow($row, $dsIndex);
            }

            $rras[$i] = [
                'rra' => $rra,
                'data' => $data
            ];
        }

        return $rras;
    }
}