<?php

function sum (array $list) {
  reset($list);
  $sum = 0;
  do { $sum += current($list); } while (next($list));
  return $sum;
}

function average (array $list) {
  return sum($list) / count($list);
}

/**
 * noasync average time: 3.95ms (errors sometimes between creation, deletion and reading)
 * async average time: 6.46ms (no error)
 */


echo "noasync\n";
$list = array();
for ($i=0; $i < 100; $i++) {
  $delay = require __DIR__ . "/no_async.php";
  array_push($list, $delay);
}

$min = min($list);
$av = average($list);
$max = max($list);
echo "min=$min\tav=$av\tmax=$max\n";
echo implode("\t", $list) . "\n";


echo "async\n";
$list = array();
for ($i=0; $i < 100; $i++) {
  $delay = require __DIR__ . "/file.php";
  array_push($list, $delay);
}

$min = min($list);
$av = average($list);
$max = max($list);
echo "min=$min\tav=$av\tmax=$max\n";
echo implode("\t", $list) . "\n";
