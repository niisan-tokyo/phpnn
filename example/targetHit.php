<?php

require('../vendor/autoload.php');

use Niisan\phpnn\layer\Relu;
use Niisan\phpnn\layer\Sin;
use Niisan\phpnn\layer\Linear;
use Niisan\phpnn\layer\HyperbolicTangent;

$bundle = new Niisan\phpnn\bundle\Simple();

$effect = 0.005;
$seperate = [1, 100, 1000, 10000, 30000, 60000, 100000];
$bundle->add(Relu::createInstance()->init(2, 32, ['effect' => $effect]));
$bundle->add(HyperbolicTangent::createInstance()->init(32, 64, ['effect' => $effect]));
$bundle->add(Relu::createInstance()->init(64, 32, ['effect' => $effect]));
$bundle->add(HyperbolicTangent::createInstance()->init(32, 1, ['effect' => $effect]));

for ($i = 1; $i < 100001; $i++) {
    $x = mt_rand(-20000, 20000) / 10000.0;
    $y = mt_rand(-20000, 20000) / 10000.0;
    $z = $bundle->exec([$x, $y]);
    $ishit = donuts($x, $y);
    $bundle->correct($ishit);

    if (in_array($i, $seperate)) {
        output($bundle, $i);
    }
}

$count = 0;
for ($i = 1; $i < 1001; $i++) {
    $x = mt_rand(-2000, 2000) / 1000.0;
    $y = mt_rand(-2000, 2000) / 1000.0;
    $z = $bundle->exec([$x, $y]);
    $s = donuts($x, $y);
    if ($z[0] * $s > 0) {
        $count++;
    }
}

$rate = $count * 100.0/ 1000;
echo "的中率: $rate %!\n";

function donuts($x, $y)
{
    $r = $x * $x + $y * $y;
    if ($r > 1 and $r < 4) {
        return 1;
    }

    return -1;
}

function output($bundle, $count)
{
    $out = '';
    for ($i = 0; $i < 40; $i++) {
        for ($j = 0; $j < 40; $j++) {
            $x = ($i - 20) / 10.0;
            $y = ($j - 20) / 10.0;
            $z = $bundle->exec([$x, $y]);
            if ($z[0] > 0) {
                $out .= "$x,$y\n";
            }
        }
    }

    file_put_contents('../dest/circle' .  $count . '.csv', $out);
}
