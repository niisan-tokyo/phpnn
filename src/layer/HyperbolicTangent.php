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
        $temp = $this->activate($val);
        return $this->max * $this->max - $temp * $temp;
    }
}
