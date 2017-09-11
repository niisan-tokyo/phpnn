<?php

namespace Niisan\phpnn\layer;

class Relu extends Base
{

    public function activate($val)
    {
        return max(0, $val);
    }

    public function defferential($val)
    {
        return ($val >= 0) ? 1: 0;
    }
}
