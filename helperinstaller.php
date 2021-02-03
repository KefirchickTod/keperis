<?php


$arguments = $argv;
$shortopts = "";
//$shortopts .= "f:";  // Обязательное значение
//$shortopts .= "v::"; // Необязательное значение
//$shortopts .= "abc"; // Эти параметры не принимают никаких значений

$longopts = array(
    "docs:"
);
$options = getopt($shortopts, $longopts);
var_dump($argv, $options);