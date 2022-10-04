<?php

$a_stairs = [];
for($i = 1; $i <= 100; $i++) {
    array_push($a_stairs, $i);
}

$output = '';
$k = 0;
foreach($a_stairs as $key => $val) {
    for($j = $key; $j >= 0; $j--) {
        $output .= $a_stairs[$k] . " ";
        $k++;
    }
    if(!empty($a_stairs[$k])) {
        $output .= "\n";
    }
}

echo $output;
echo "\n";




