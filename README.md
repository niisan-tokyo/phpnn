# phpnn
The neural network (NN) written by PHP.

## Installation
You can get phpnn using composer with following command:

```
composer require niisan-tokyo/phpnn:dev-master
```

## Usage
We can use NN function approximation with php easily.

### use as a function
```
<?php
require('vendor/autoload.php');

use Niisan\phpnn\layer\Relu;
use Niisan\phpnn\layer\Linear;

$bundle = new Niisan\phpnn\bundle\Simple();

// input parameter dimension is 1, output dimension is 64
$bundle->add(new Relu(32), ['input_dim' => 2]);
$bundle->add(new Linear(64));

$o = $bundle->exec(1.0);
echo $o[0];
```

### learning with training data
```
<php
require('../vendor/autoload.php');

use Niisan\phpnn\layer\Relu;
use Niisan\phpnn\layer\Linear;
use Niisan\phpnn\layer\HyperbolicTangent;
use Niisan\phpnn\bundle\Simple;

// output model file
$model_filename = '../dest/targetHitModel';
$epoch  = 100;
$effect = 0.005;

$bundle = new Simple();

$bundle->add(new Relu(32), ['input_dim' => 2]);
$bundle->add(new HyperbolicTangent(64));
$bundle->add(new Relu(32));
$bundle->add(new HyperbolicTangent(1));

$training_data = getTrainingData();// get data sets.

// execute learning
$bundle->fit([$trainX, $trainY], [
  'epoch' => $epoch,
  'effect' => $effect,
  'batch_size' => 16
]);

// save learning model
$bundle->save($model_filename);
```
