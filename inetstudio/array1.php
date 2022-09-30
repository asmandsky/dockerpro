<?php

$array = [
    [id => 1, date => "12.01.2020", name => "test1"],
    [id => 2, date => "02.05.2020", name => "test2"],
    [id => 4, date => "08.03.2020", name => "test4"],
    [id => 1, date => "22.01.2020", name => "test1"],
    [id => 2, date => "11.11.2020", name => "test4"],
    [id => 3, date => "06.06.2020", name => "test3"],
];

$ids = array_column($array,'id');
$unique = array_unique($ids);
$array = array_filter($array, function ($key, $val) use ($unique) {
    return in_array($val, array_keys($unique));
},ARRAY_FILTER_USE_BOTH);

echo "\n1) выделить уникальные записи (убрать дубли) в отдельный массив. в конечном массиве не должно быть элементов с одинаковым id.\n";
print_r($array);
$array1 = $array;

function checkData(string $key, array $_value): string
{
    $value = $_value[$key];
    switch ($key) {
        case 'date':
            $value = strtotime($value);
            break;
        default:
            break;
    }
    return $value;
}

$key = 'date';
$sort = 'asc';

uksort($array, function ($key1, $key2) use ($array, $key, $sort) {
    $value2 = checkData($key, $array[$key2]);
    $value1 = checkData($key, $array[$key1]);
    if ($value2 == $value1) {
        return 0;
    }
    if ($sort == 'asc')
        return ($value1 > $value2) ? 1 : -1;
    else
        return ($value1 > $value2) ? -1 : 1;
});

echo "\n2) отсортировать многомерный массив по ключу (любому)\n";
print_r($array);

$condition = [id => 2];
$_key = key($condition);
$array = array_filter($array, function ($key) use ($condition, $_key) {
    return (!empty($key[$_key]) && $key[$_key] == $condition[$_key]);
},ARRAY_FILTER_USE_BOTH);

echo "\n3) вернуть из массива только элементы, удовлетворяющие внешним условиям (например элементы с определенным id)\n";
print_r($array);

$ids1 = array_column($array1, 'id', 'name');

echo "\n4) изменить в массиве значения и ключи (использовать name => id в качестве пары ключ => значение)\n";
print_r($ids1);



