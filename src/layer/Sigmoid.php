<?php

namespace Niisan\phpnn\layer;

class Sigmoid extends Base
{

    public function activate($val)
    {
        return (tanh($val / 2) + 1) / 2;
    }

    public function defferential($val)
    {
        return $this->activate($val) * (1 - $this->activate($val));
    }
}
