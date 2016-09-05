<?php

namespace layer;

use Niisan\phpnn\layer\HyperbolicTangent;

class HyperbolicTangentTest extends \TestCase
{

    public function testPropegate()
    {
        $obj = new HyperbolicTangent();
        $obj->init(1, 1, ['max' => 2, 'offset' => 1]);

        $file = __DIR__ . '/../data/out/ht.txt';
        file_put_contents($file, serialize([[1]]));
        $obj->load($file);
        $ret = $obj->prop([1]);

        $this->assertEquals(2 * (tanh(1) + 1), $ret[0]);

        $back = $obj->backProp([1]);
        $test = 2 * (1 - tanh(1) * tanh(1));
        $this->assertEquals($test, $back[0]);
    }
}
