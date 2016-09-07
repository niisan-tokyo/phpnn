<?php

require('../vendor/autoload.php');

use Niisan\phpnn\layer\Relu;
use Niisan\phpnn\layer\Linear;

$bundle = new Niisan\phpnn\bundle\Simple();

$bundle->add(Relu::createInstance()->init(1, 64, ['effect' => 0.3]));
$bundle->add(Relu::createInstance()->init(64, 128, ['effect' => 0.3]));
$bundle->add(Relu::createInstance()->init(128, 64, ['effect' => 0.3]));
$bundle->add(Linear::createInstance()->init(64, 1, ['effect' => 0.3]));

for ($i = 0; $i < 100000; $i++) {
    $x = mt_rand(-100000, 100000) * pi() / 200000;
    $y = $bundle->exec($x);
    if ($i % 1000 == 0) {
        echo "value = $y[0] \n";
    }
    $bundle->correct(sin($x));
}

echo "End of machine learning!\n";

$x = mt_rand(-100000, 100000) * pi() / 200000;
echo "input: $x \n";
echo "real:  ". sin($x) . "\n";
$y = $bundle->exec($x);
$o = $y[0];
echo "appr:  $o \n";
