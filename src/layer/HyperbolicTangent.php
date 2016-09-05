<?php

namespace Niisan\phpnn\layer;

class HyperbolicTangent extends Base
{

    protected $max = 2;
    protected $offset = 0;

    public function activate($val)
    {
        return $this->max * (tanh($val) + $this->offset) ;
    }

    public function defferential($val)
    {
        $temp = tanh($val);
        return $this->max *(1 - $temp * $temp);
    }
}
