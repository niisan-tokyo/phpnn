<?php

require('../vendor/autoload.php');

use Niisan\phpnn\layer\Relu;
use Niisan\phpnn\layer\HyperbolicTangent;
use Niisan\phpnn\layer\Linear;

$bundle = new Niisan\phpnn\bundle\Simple();

$effect = 0.1;
$seperate = 10000;
$bundle->add(Relu::createInstance()->init(1, 64, ['effect' => $effect]));
$bundle->add(HyperbolicTangent::createInstance()->init(64, 64, ['effect' => $effect]));
$bundle->add(Relu::createInstance()->init(64, 64, ['effect' => $effect]));
$bundle->add(Linear::createInstance()->init(64, 1, ['effect' => $effect]));

$check = [];
$title = [];
for ($i = 1; $i < 30001; $i++) {
    $x = mt_rand(-10000, 10000) * pi() / 20000;
    $y = $bundle->exec($x);
    $bundle->correct(sin($x));
    if ($i % $seperate === 0) {
        $check[] = check($bundle);
        $title[] = $i;
    }
}

echo "End of machine learning!\n";

array_unshift($title, 'x');
$str = implode(',', $title) . "\n";
$loss = 0;
for ($i = -40; $i != 41; $i++) {
    $x = pi() * $i / 80;
    $z = sin($x);
    $str .= $x . ',';
    foreach ($check as $val) {
        $str .= $val[$i + 40] .  ',';
    }
    $str .= $z . "\n";
}

file_put_contents('../dest/sin.csv', $str);

function check($bundle)
{
    $loss = 0;
    $ret = [];
    for ($i = -40; $i != 41; $i++) {
        $x = pi() * $i / 80;
        $y = $bundle->exec($x);
        $z = sin($x);
        $ret[] = $y[0];
        $loss += ($y[0] - $z) * ($y[0] - $z);
    }

    echo "summary loss: $loss \n";
    return $ret;
}
