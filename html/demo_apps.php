<?php
require __DIR__ . '/lib.php';

$start  = microtime(TRUE);
echo "<pre>\n";
for ($x = 1; $x < 10; $x++) {
	// echo ntp();
    echo "\nLorem Ipsum  : **************************************\n";
	echo ipsum();
    echo "\nPrime Numbers: **************************************\n";
	echo prime();
    echo "\Weather: **************************************\n";
	echo weather();
    ob_flush();
}
echo "\n\nElapsed Time: " . (microtime(TRUE) - $start) . "\n";
echo "</pre>\n";

// 10.089123010635
//  6.6656608581543
//  6.5485470294952
//  6.4534080028534
