<?php

namespace layer;

use Niisan\phpnn\layer\Relu;

class ReluTest extends \TestCase
{
    public function testPropergate()
    {
        $file = __DIR__ . '/../data/out/output.txt';
        $obj = new Relu();
        $obj->init(3, 3, ['effect' => 2]);
        $obj->save($file);

        $matrix = unserialize(file_get_contents($file));
        $i = 0;
        foreach ($matrix as $col) {
            foreach ($col as $val) {
                $i++;
                $this->assertTrue($val * $val <= 1);
            }
        }
        $this->assertEquals(9, $i);

        // make test data
        $test_mat = [
            [1, 1, 1],
            [1, -1, -1],
            [-1, 1, 1]
        ];
        $rfile = __DIR__. '/../data/out/relu.txt';
        file_put_contents($rfile, serialize($test_mat));
        $obj->load($rfile);

        $ret = $obj->prop([1, 0.5, -1]);
        $this->assertEquals(0.5, $ret[0]);
        $this->assertEquals(1.5, $ret[1]);
        $this->assertEquals(0, $ret[2]);// relu

        // back propergate
        $ret = $obj->backProp([-1, 2, 1]);
        // delta = [-1 * 1, 2 * 1, 1 * 0]
        $this->assertEquals(1, $ret[0]);// [1, 1, -1] * [-1, 2, 0]
        $this->assertEquals(-3, $ret[1]);//[1, -1, 1] * [-1, 2, 0]
        $this->assertEquals(-3, $ret[2]);//[1, -1, 1] * [-1, 2, 0]

        $obj->save($file);

        // check weight after the backProp
        $matrix = unserialize(file_get_contents($file));
        // y = [1, 0.5, -1] , delta = [-1, 2, 0]
        $this->assertEquals(3, $matrix[0][0]);//1 - (2 * (-1)) = 3
        $this->assertEquals(2, $matrix[0][1]);//1 - 2 * 0.5 *(-1) = 2
        $this->assertEquals(-1, $matrix[0][2]);//1 - 2 * 1 = -1
        $this->assertEquals(-3, $matrix[1][0]);
        $this->assertEquals(-3, $matrix[1][1]);
        $this->assertEquals(3, $matrix[1][2]);
        $this->assertEquals(-1, $matrix[2][0]);
        $this->assertEquals(1, $matrix[2][1]);
        $this->assertEquals(1, $matrix[2][2]);
    }
}
