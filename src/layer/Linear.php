<?php

namespace Niisan\phpnn\layer;

class Linear extends Base
{

    public function activate($val)
    {
        return $val;
    }

    public function defferential($val)
    {
        return 1;
    }
}
