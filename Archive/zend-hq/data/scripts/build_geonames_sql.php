<?php
// see: https://download.geonames.org/export/zip/
define('SRC_FN', __DIR__ . '/US.txt');
define('DEST_FN', __DIR__ . '/geonames.sql');
$src = new SplFileObject(SRC_FN, 'r');
$fmt = "(%d,'%s',%d,'%s','%s','%s','%s','%s','%s','%s',%8.4f,%8.4f,%d),\n";
$sql = <<<EOT
CREATE TABLE IF NOT EXISTS `geonames` (
  `id` int(8) unsigned NOT NULL auto_increment,
  `iso2` char(2) NOT NULL,
  `post_code` int(5) NOT NULL,
  `city` varchar(64) COLLATE utf8_general_ci NOT NULL,
  `state_name` varchar(64) COLLATE utf8_general_ci NOT NULL,
  `state_code` char(2) COLLATE utf8_general_ci NOT NULL,
  `county` varchar(64) COLLATE utf8_general_ci NOT NULL default '',
  `county_code` char(4) COLLATE utf8_general_ci NOT NULL default '',
  `other` varchar(64) COLLATE utf8_general_ci NOT NULL default '',
  `other_code` char(4) COLLATE utf8_general_ci NOT NULL default '',
  `latitude` char(16) NOT NULL default '',
  `longitude` char(16) NOT NULL default '',
  `accuracy` decimal unsigned NOT NULL default 0,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`)
)
INSERT INTO geonames
(`id`,`iso2`,`post_code`,`city`,`state_name`,`state_code`,`county`,`county_code`,`other`,`other_code`,`latitude`,`longitude`,`accuracy`)
VALUES

EOT;
$count = 1;
while ($line = $src->fgetcsv("\t")) {
    if (!empty($line) && is_array($line) && count($line) === 12) {
        array_unshift($line, $count++);
        $sql .= vsprintf($fmt, $line);
    }
}
$sql[-1] = ';';
echo 'Writing SQL into ' . DEST_FN . "\n";
file_put_contents(DEST_FN, $sql);



