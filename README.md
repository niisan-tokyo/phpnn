# phpnn
The neural network (NN) written by PHP.

## Installation
You can get phpnn using composer with following command:

```
composer require niisan-tokyo/phpnn:php-dev
```

## Usage
We can use NN function approximation with php easily.

```
<?php
require('vendor/autoload.php');

use Niisan\phpnn\layer\Relu;
use Niisan\phpnn\layer\Linear;

$bundle = new Niisan\phpnn\bundle\Simple();

// input parameter dimension is 1, output dimension is 64, learning rate is 0.1
$bundle->add(Relu::createInstance()->init(1, 64, ['effect' => 0.1]));

$bundle->add(Linear::createInstance()->init(64, 1, ['effect' => 0.1]));

$o = $bundle->exec(1.0);
echo $o[0];
```
