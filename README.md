# Pure PHP RRDtool file reader
[![Build Status](https://travis-ci.org/aldas/rrd-php-reader.svg?branch=master)](https://travis-ci.org/aldas/rrd-php-reader)
[![codecov](https://codecov.io/gh/aldas/rrd-php-reader/branch/master/graph/badge.svg)](https://codecov.io/gh/aldas/rrd-php-reader)


> Because on windows php ext-rrd can not read rrd files created on unix and vice versa.
> See https://github.com/oetiker/rrdtool-1.x/issues/759

This library supports reading:
* rrds created on 64bit linux from 64bit Windows
* rrds created on 64bit Windows from 64bit Linux

**Only meant to export/dump data out of rrd file.**

This library is based on [javascriptRRD](http://javascriptrrd.sourceforge.net/)

## Example

Convert RRD to CSV:  [rrd_to_csv.php](examples/rrd_to_csv.php)
```
$reader = RrdReader::createFromPath('path/to/my_rrd.rrd');

$fp = fopen('output.csv', 'wb');
$reader->outputAsCsv($fp, [
    'ds' => 'value'
]);
fclose($fp);
```


Filter rrd: [read_rrd.php](examples/read_rrd.php)
```
$reader = RrdReader::createFromPath('path/to/my_rrd.rrd');

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

```

Output would be:
```
timestamp=1521054891, value=6.000000, cf=AVERAGE, ds=value, step=1
timestamp=1521054892, value=7.000000, cf=AVERAGE, ds=value, step=1
timestamp=1521054891, value=6.000000, cf=MAX, ds=value, step=1
timestamp=1521054892, value=7.000000, cf=MAX, ds=value, step=1
```
