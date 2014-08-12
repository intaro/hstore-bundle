<?php

require __DIR__ . '/../../HStore/Exception/ConversionException.php';
require __DIR__ . '/../../HStore/HStoreParser.php';

$examples = require __DIR__ . '/../Resources/strings.php';

function show_report($type, $time, $counter, $mpu, $mu)
{
    echo $type . ": " . ($time * 1000) . " ms for $counter strings\n";
    echo "Memory peak usage: " . ($mpu / 1024 / 1024) . " MB\n";
    echo "Memory usage: " . ($mu / 1024 / 1024) . " MB\n";
    echo "\n";
}

$parser1 = new Intaro\HStoreBundle\HStore\HStoreParser();
$parser2 = new HStore\HStoreParser();

//////////////////////////////////////////////////

echo "Checking execution:\n";
for ($i = 0, $n = sizeof($examples); $i < $n; $i++) {
    $str1 = $parser1->parse($examples[$i]);
    $str2 = $parser2->parse($examples[$i]);

    echo ($str1 == $str2) ? '+' : '-';
}
echo "\n";

//////////////////////////////////////////////////

$t = microtime(true);
$counter = 0;
for ($j = 0; $j < 10; $j++) {
    for ($i = 0, $n = sizeof($examples); $i < $n; $i++) {
        $parser1->parse($examples[$i]);
        $counter++;
    }
}
$t = microtime(true) - $t;

show_report('HStoreParser', $t, $counter, memory_get_peak_usage(), memory_get_usage());

//////////////////////////////////////////////////

$t = microtime(true);
$counter = 0;
for ($j = 0; $j < 10; $j++) {
    for ($i = 0, $n = sizeof($examples); $i < $n; $i++) {
        $parser2->parse($examples[$i]);
        $counter++;
    }
}
$t = microtime(true) - $t;

show_report('Zephir HStoreParser', $t, $counter, memory_get_peak_usage(), memory_get_usage());

//////////////////////////////////////////////////

