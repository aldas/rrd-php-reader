# Pure PHP RRDtool file reader

> Because on windows php ext-rrd can not read rrd files created on unix.

**Only meant to export data out of rrd file. Not support for consolidation function etc**

Original repository: http://javascriptrrd.sourceforge.net/

## Example

```
$reader = RrdReader::createFromPath('path/to/my_rrd.rrd');

$data = $reader->setDs('value')->setRraIndex(0)->getAsArray();
```