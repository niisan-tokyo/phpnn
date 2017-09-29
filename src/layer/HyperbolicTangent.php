<?php

namespace Niisan\phpnn\layer;

class HyperbolicTangent extends Base
{

    protected $max_value = 1;
    protected $offset = 0;

    public function activate($val)
    {
        return $this->max_value * (tanh($val) + $this->offset) ;
    }

    public function defferential($val)
    {
        $temp = tanh($val);
        return $this->max_value *(1 - $temp * $temp);
    }
}
