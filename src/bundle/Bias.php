<?php

namespace Niisan\phpnn\bundle;

class Bias extends Base
{

    private $input;

    public function exec($state)
    {
        $this->input = $state;
        return $this->foreLoop($state);
    }

    public function correct($state)
    {
        $count = 0;
        while ($count == 0 or $this->loss($state) > 0.0001 and $count < 100) {
            $this->backLoop($state);
            $this->foreLoop($this->input);
            $count++;
        }
    }
}
