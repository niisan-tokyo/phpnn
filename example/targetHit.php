<?php

/**
 * ドーナツ型の領域 1 < x^2 + y^2 < 4 に(x, y)の組があったときだけ反応するような系を考える。
 * 現在、その系がどのようなものかはわからないと仮定し、実験を繰り返すことで(x, y)に対する
 * 反応を観察し、データをためたと仮定する
 *
 * この状態で機械学習機構にたまったデータセットを投入することで、
 * 反応領域を学習機が十分推定できる状態になるか実験する
 *
 * また、データをセーブできるようにしておき、一度保存したモデルは後で読み出せるようにしておく
 */

require('../vendor/autoload.php');

use Niisan\phpnn\layer\Relu;
use Niisan\phpnn\layer\Sigmoid;
use Niisan\phpnn\layer\Linear;
use Niisan\phpnn\layer\HyperbolicTangent;
use Niisan\phpnn\bundle\Simple;

// モデルの出力先
$model_filename = '../dest/targetHitModel';
$epoch  = 100;
$effect = 0.005;

// すでに出力済みのモデルがあれば、それを読み込み、無ければモデルを構築する
if (file_exists($model_filename)) {
    $bundle = Simple::load($model_filename);
} else {
    $bundle = new Simple();

    $bundle->add(new Relu(32), ['input_dim' => 2]);
    $bundle->add(new Sigmoid(64), ['max_value' => 2]);
    $bundle->add(new Relu(32));
    $bundle->add(new HyperbolicTangent(1), ['max_value' => 2]);
}

// データセットを作成する
$trainX = [];
$trainY = [];
$testX = [];
$testY = [];
for ($i = 0; $i < 10000; $i++) {
    $x = mt_rand(-2000, 2000) / 1000.0;
    $y = mt_rand(-2000, 2000) / 1000.0;
    $z = [$x, $y];
    $ishit = donuts($x, $y);

    if ($i < 9500) {
        $trainX[] = $z;
        $trainY[] = $ishit;
    } else {
        $testX[] = $z;
        $testY[] = $ishit;
    }
}

// フィッティングする
$bundle->fit([$trainX, $trainY], [
    'epoch' => $epoch,
    'test' => [$testX, $testY],
    'effect' => $effect,
    'batch_size' => 16,
    'accuracy_callback' => function($z, $t){
        return $z[0] * $t > 0;
    }
]);
$bundle->save($model_filename);

// 図に書き出す
output($bundle, $epoch);

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
    for ($i = 0; $i < 60; $i++) {
        for ($j = 0; $j < 60; $j++) {
            $x = ($i - 30) / 10.0;
            $y = ($j - 30) / 10.0;
            $z = $bundle->exec([$x, $y]);
            if ($z[0] > 0) {
                $out .= "$x,$y\n";
            }
        }
    }

    file_put_contents('../dest/circle' .  $count . '.csv', $out);
}
