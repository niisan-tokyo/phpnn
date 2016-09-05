<?php

namespace Niisan\phpnn\layer;

class HyperboricTangent extends Base
{

    protected $max = 2;

    public function activate($val)
    {
        return $this->max * tanh($val);
    }

    public function defferential($val)
    {
        $temp = tanh($val);
        return $this->max *(1 - $temp * $temp);
    }
}
