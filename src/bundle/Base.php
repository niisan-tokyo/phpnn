<?php
namespace Niisan\phpnn\bundle;

use ProgressBar\Manager as Progress;

abstract class Base
{
    protected $bundle = [];
    private $value = [];
    private $epoch = 0;

    private $last_dimension;

    protected $loss_val = 0;

    public function add($obj, $option)
    {
        $this->bundle[] = $obj;

        $input_dim = $this->last_dimension ?? $option['input_dim'];
        $obj->init($input_dim, $option);
        $this->last_dimension = $obj->getOutputDim();
    }

    public function fit($train, $option)
    {
        $X = $train[0];
        $Y = $train[1];
        $epoch = $option['epoch'] ?? 1;
        for ($i = 1; $i < $epoch + 1; $i++) {
            echo "$i / $epoch \n";
            $prog = new Progress(0, count($X));
            foreach ($X as $key => $val) {
                $this->exec($val);
                $this->correct($Y[$key]);
                $prog->advance();
            }

            if (isset($option['test'])) {
                $method = $option['test_function'] ?? 'loss';
                $this->{'test' . ucfirst($method)}($option['test']);
            }

        }

    }

    abstract public function exec($value);

    abstract public function correct($value);

    protected function foreLoop($state)
    {
        if (!is_array($state)) {
            $state = [$state];
        }

        $ret = $state;
        foreach ($this->bundle as $obj) {
            $ret = $obj->prop($ret);
        }

        $this->value = $ret;
        return $ret;
    }

    protected function backLoop($state)
    {
        $diff = [];
        $state = (is_array($state)) ? $state : [$state];
        $diff = $this->lossDiff($state);

        $this->lossDisp($state);

        $ref = count($this->bundle) - 1;
        for ($i = $ref; $i >= 0; $i--) {
            $diff = $this->bundle[$i]->backProp($diff);
        }

        $this->epock++;
    }

    protected function lossDiff($state)
    {
        $ret = [];
        foreach ($state as $key => $val) {
            $ret[$key] = $this->value[$key] - $val;
        }

        return $ret;
    }

    protected function loss($state)
    {
        $loss = 0;
        $state = (is_array($state)) ? $state : [$state];
        foreach ($state as $key => $val) {
            $loss += ($this->value[$key] - $val) * ($this->value[$key] - $val) / 2;
        }

        return $loss;
    }

    protected function lossDisp($state)
    {
        if ($this->epoch % 1000 == 0) {
            $loss = $this->loss($state);
            //echo "loss: $loss \n";
        }
    }

    protected function dropSwitch()
    {
        foreach ($this->bundle as $layer) {
            $layer->switchDrop();
        }
    }

    protected function testLoss($test_data)
    {
        $X = $test_data[0];
        $Y = $test_data[1];
        //print_r($test_data);
        $loss = 0;
        foreach ($X as $key => $val) {
            $res = $this->exec($val);
            //print_r($res);
            $loss += $this->loss($Y[$key]);
        }

        echo "loss: $loss \n";
    }

    /**
     * 現在のモデルの状況を保存する
     *
     * @param  string $file ファイル名
     */
    public function save(string $file)
    {
        $str = serialize($this);
        file_put_contents($file, $str);
    }

    /**
     * モデルをロードする
     *
     * @param  string $file ファイル名
     *
     * @return static       モデルのインスタンスを返却する
     */
    public static function load(string $file)
    {
        $data = file_get_contents($file);
        return unserialize($data);
    }

    public function __sleep()
    {
        return ['bundle'];
    }

    public function __wakeup(){}
}
