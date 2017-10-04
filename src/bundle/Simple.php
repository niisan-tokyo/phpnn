<?php

namespace Niisan\phpnn\bundle;

class Simple extends Base
{

    public function exec($value, $stop = -1)
    {
        $ret = $this->foreLoop($value, $stop);
        return $ret;
    }

    public function correct($value)
    {
        $this->backLoop($value);
    }

    public function switch()
    {
        $this->dropSwitch();
    }
}
