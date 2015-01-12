<?php

if(count($argv) < 2)
{
    echo '引数エラー' . PHP_EOL;
    exit;
}

require_once 'ImageConvert.php';

$url = $argv[1];

$convert = new ImageConvert();
$convert->setUrl($url);
$result  = $convert->execute();

if($error = $convert->getError())
{
    echo $error . PHP_EOL;
    exit;
}

$fileName  = __DIR__ . '/' . date('Ymdhis') . '.png';
$imageData = file_get_contents($convert->getImageUrl());

file_put_contents($fileName, $imageData);
