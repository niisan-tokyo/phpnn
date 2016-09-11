<?php

namespace Niisan\phpnn\bundle;

class Simple extends Base
{

    public function exec($value)
    {
        $ret = $this->foreLoop($value);
        return $ret;
    }

    public function correct($value)
    {
        $this->backLoop($value);
    }
}
