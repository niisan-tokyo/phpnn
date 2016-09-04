<?php

namespace Niisan\phpnn\layer;

class Relu extends Base
{

    public function activate($val)
    {
        if ($val > 0) {
            return $val;
        }

        return 0;
    }

    public function defferential($val)
    {
        if ($val > 0) {
            return 1;
        }

        return 0;
    }
}
