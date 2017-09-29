<?php

require('../vendor/autoload.php');

use Niisan\phpnn\layer\Relu;
use Niisan\phpnn\layer\HyperbolicTangent;
use Niisan\phpnn\layer\Linear;
use Niisan\phpnn\bundle\Simple;



$effect = 0.001;
$epoch  = 10;
$model_filename = '../dest/sinApproxModel';

if (file_exists($model_filename)) {
    $bundle = Simple::load($model_filename);
} else {
    $bundle = new Niisan\phpnn\bundle\Simple();

    $bundle->add(new Relu(32), ['input_dim' => 1]);
    $bundle->add(new HyperbolicTangent(64));
    $bundle->add(new Relu(32));
    $bundle->add(new Linear(1));
}

$trainX = [];
$trainY = [];
$testX  = [];
$testY  = [];

for ($i = 0; $i < 20000; $i++) {
    $x = mt_rand(-20000, 20000) * pi() / 2000;
    $y = sin($x);// * cos($x);
    if ($i < 19000) {
        $trainX[] = $x;
        $trainY[] = $y;
    } else {
        $testX[] = $x;
        $testY[] = $y;
    }
}

// フィッティングする
$bundle->fit([$trainX, $trainY], ['epoch' => $epoch, 'test' => [$testX, $testY], 'batch_size' => 16, 'effect' => $effect]);
$bundle->save($model_filename);

$data = check($bundle);
$str = '';
foreach ($data as $val) {
    $str .= implode(',', $val) . "\n";
}
file_put_contents('../dest/sin' . $epoch . '.csv', $str);

// $check = [];
// $title = [];
// for ($i = 1; $i < 120001; $i++) {
//     $x = mt_rand(-1000, 1000) * pi() / 2000;
//     $y = $bundle->exec($x);
//     $bundle->correct(sin($x)*cos($x));
//     if ($i % $seperate === 0) {
//         $check[] = check($bundle);
//         $title[] = $i;
//     }
// }
// $bundle->switch();

echo "End of machine learning!\n";

function check($bundle)
{
    //$bundle->switch();
    $loss = 0;
    $ret = [];
    for ($i = -400; $i != 401; $i++) {
        $x = pi() * $i / 100;
        $y = $bundle->exec($x);
        $z = sin($x);//*cos($x);
        $ret[] = [$x, $y[0]];
        $loss += ($y[0] - $z) * ($y[0] - $z);
    }

    echo "summary loss: $loss \n";
    //$bundle->switch();
    return $ret;
}
