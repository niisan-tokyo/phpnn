<?php

namespace Niisan\phpnn\bundle;

class Simple
{
    private $bundle = [];
    private $value = [];
    private $epock = 0;

    public function add($obj)
    {
        $this->bundle[] = $obj;

    }

    public function exec($value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        $ret = $value;
        foreach ($this->bundle as $obj) {
            $ret = $obj->prop($ret);
        }

        $this->value = $ret;
        return $ret;
    }

    public function correct($value)
    {
        $diff = [];
        $value = (is_array($value)) ? $value : [$value];
        foreach ($value as $key => $val) {
            $diff[$key] = $this->value[$key] - $val;
        }

        if ($this->epock % 1000 == 0) {
            echo "loss:\n";
            foreach ($diff as $val) {
                echo "    " . ($val * $val / 2) . "\n";
            }
        }

        for ($i = count($this->bundle) - 1; $i >= 0; $i--) {
            $diff = $this->bundle[$i]->backProp($diff);
        }

        $this->epock++;
    }
}
