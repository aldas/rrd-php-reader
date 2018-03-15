# Pure PHP RRDtool file reader

> Because on windows php ext-rrd can not read rrd files created on unix and vice versa.
> See https://github.com/oetiker/rrdtool-1.x/issues/759

This library supports reading:
* rrds created on 64bit linux from 64bit Windows
* rrds created on 64bit Windows from 64bit Linux

**Only meant to export/dump data out of rrd file. No support for consolidation function etc**

Original repository: http://javascriptrrd.sourceforge.net/

## Example

```
use RrdPhpReader\RrdReader;

$reader = RrdReader::createFromPath('path/to/my_rrd.rrd');

$data = $reader->setDs('value')->setRraIndex(0)->getAsArray();

```

`$data` structure would be
```
[
    'value' => [ <--- datasource as first level keys
        0 => [   <--- rra index as key
            1521054891 => 6,  <--- timestamp => value
            1521054892 => 7,
            1521054893 => 8,
            1521054894 => 9,
        ]
    ]
];
```
