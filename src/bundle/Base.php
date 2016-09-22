<?php
namespace Niisan\phpnn\bundle;

abstract class Base
{
    private $bundle = [];
    private $value = [];
    private $epock = 0;

    protected $loss_val = 0;

    public function add($obj)
    {
        $this->bundle[] = $obj;

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
        if ($this->epock % 1000 == 0) {
            $loss = $this->loss($state);
            echo "loss: $loss \n";
        }
    }

    protected function dropSwitch()
    {
        foreach ($this->bundle as $layer) {
            $layer->switchDrop();
        }
    }
}
