<?php

namespace Niisan\phpnn\layer;

class Sin extends Base
{

    protected $max = 2;
    protected $offset = 0;

    public function activate($val)
    {
        return $this->max * (sin($val) + $this->offset) ;
    }

    public function defferential($val)
    {
        return $this->max * cos($val);
    }
}
