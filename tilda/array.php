<?php

$fill = array_fill(0, 1000, 0);
$keys = array_keys($fill);
shuffle($keys);
$output = array_slice($keys, 0, 5*7);

$value1 = $value2 = $two_arr = [];
$i = 0;
for($c = 0; $c < 5; $c++) {
    for($r = 0; $r < 7; $r++) {
        $two_arr[$c][$r] = $output[$i];
        $i++;
    }
}

print_r($two_arr);

foreach($two_arr as $key => &$value) {
    $value1[$key] = array_sum($value);
}

print_r($value1);

$two_arr1 = $two_arr;
array_unshift($two_arr1, null);
$two_arr1 = call_user_func_array("array_map", $two_arr1);

foreach($two_arr1 as $key => &$value) {
    $value2[$key] = array_sum($value);
}

print_r($value2);
