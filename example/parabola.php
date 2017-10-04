<?php
require('../vendor/autoload.php');

use Niisan\phpnn\layer\Relu;
use Niisan\phpnn\layer\Sigmoid;
use Niisan\phpnn\layer\HyperbolicTangent;
use Niisan\phpnn\bundle\Simple;

// output model file
$model_filename = '../dest/parabolaModel';
$epoch  = 30;
$effect = 0.02;

$bundle = new Simple();

$bundle->add(new Relu(32), ['input_dim' => 2]);
$bundle->add(new Sigmoid(64));
$bundle->add(new Relu(32));
$bundle->add(new HyperbolicTangent(1));

list($trainX, $trainY) = getTrainingData();

// execute learning
$bundle->fit([$trainX, $trainY], [
  'epoch' => $epoch,
  'effect' => $effect,
  'batch_size' => 1
]);

// save learning model
$bundle->save($model_filename);

function getTrainingData()
{
    $X = [];
    $Y = [];
    for ($i = 0; $i < 1000; $i++) {
        $x = mt_rand(-1000, 1000) / 1000;
        $y = mt_rand(0, 1000) / 1000;
        $res = ($y > $x ** 2)? 1: -1;
        $X[$i] = [$x, $y];
        $Y[$i] = $res;
    }

    return [$X, $Y];
}
