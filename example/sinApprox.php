<?php

require('../vendor/autoload.php');

use Niisan\phpnn\layer\Relu;
use Niisan\phpnn\layer\HyperbolicTangent;
use Niisan\phpnn\layer\Linear;

$bundle = new Niisan\phpnn\bundle\Simple();

$effect = 0.1;
$seperate = 40000;
$bundle->add(Relu::createInstance()->init(1, 64, ['effect' => $effect]));
$bundle->add(HyperbolicTangent::createInstance()->init(64, 64, ['effect' => $effect]));
$bundle->add(Relu::createInstance()->init(64, 64, ['effect' => $effect, 'dropout' => 0.5]));
$bundle->add(Linear::createInstance()->init(64, 1, ['effect' => $effect]));

$check = [];
$title = [];
for ($i = 1; $i < 120001; $i++) {
    $x = mt_rand(-1000, 1000) * pi() / 2000;
    $y = $bundle->exec($x);
    $bundle->correct(sin($x)*cos($x));
    if ($i % $seperate === 0) {
        $check[] = check($bundle);
        $title[] = $i;
    }
}
$bundle->switch();

echo "End of machine learning!\n";

array_unshift($title, 'x');
$str = implode(',', $title) . ",sin\n";
$loss = 0;
for ($i = -40; $i != 41; $i++) {
    $x = pi() * $i / 80;
    $z = sin($x)*cos($x);
    $str .= $x . ',';
    foreach ($check as $val) {
        $str .= $val[$i + 40] .  ',';
    }
    $str .= $z . "\n";
}

file_put_contents('../dest/sin.csv', $str);

function check($bundle)
{
    $bundle->switch();
    $loss = 0;
    $ret = [];
    for ($i = -40; $i != 41; $i++) {
        $x = pi() * $i / 80;
        $y = $bundle->exec($x);
        $z = sin($x)*cos($x);
        $ret[] = $y[0];
        $loss += ($y[0] - $z) * ($y[0] - $z);
    }

    echo "summary loss: $loss \n";
    $bundle->switch();
    return $ret;
}
