<?php

namespace Niisan\phpnn\layer;

abstract class Base
{
    private $input_dim = 0;
    private $output_dim = 0;
    private $matrix = [];
    private $state_history = [];
    private $input_history = [];
    private $effect = 0.01;
    private $history_count = 1;

    public function createInstance()
    {
        return new static;
    }

    public function init($input_dim, $output_dim, $option = [])
    {
        echo "$input_dim, $output_dim \n";
        $this->input_dim = $input_dim;
        $this->output_dim = $output_dim;
        for ($i = 0; $i < $output_dim; $i++) {
            for ($j = 0; $j < $input_dim; $j++) {
                $this->matrix[$i][$j] = self::nonzero_rand() / $input_dim;
            }
        }

        foreach ($option as $key => $val) {
            $this->{$key} = $val;
        }

        return $this;
    }

    public function prop($states)
    {
        $ret = [];
        for ($i = 0; $i < $this->output_dim; $i++) {
            $temp = 0;
            for ($j = 0; $j < $this->input_dim; $j++) {
                $temp += $this->matrix[$i][$j] * $states[$j];
            }
            $ret[$i] = $this->activate($temp);
            $param[$i] = $temp;
        }

        $this->state_history[] = $states;
        $this->input_history[] = $param;
        if (count($this->state_history) > $this->history_count) {
            array_shift($this->state_history);
            array_shift($this->input_history);
        }

        return $ret;
    }

    public function backProp($states, $num = 0)
    {
        $ret = array_fill(0, $this->input_dim, 0);
        $history_count = count($this->state_history);
        $ref = $history_count - 1 - $num;
        $back_state = $this->state_history[$ref];
        $back_input = $this->input_history[$ref];
        $delta = [];
        for ($i = 0; $i < $this->output_dim; $i++) {
            $delta[$i] = $this->defferential($back_input[$i]) * $states[$i];
            for ($j = 0; $j < $this->input_dim; $j++) {
                $ret[$j] += $this->matrix[$i][$j] * $delta[$i];
                $this->matrix[$i][$j] -= $this->effect * $delta[$i] * $back_state[$j];
            }
        }

        return $ret;
    }

    public function reset()
    {
        $this->state_history = [];
        $this->input_history = [];
    }

    public function save($file)
    {
        //print_r($this->matrix);
        file_put_contents($file, serialize($this->matrix));
    }

    public function load($file)
    {
        $str = file_get_contents($file);
        $this->matrix = unserialize($str);
    }

    public function export()
    {
        return $this->matrix;
    }


    public function getMatrix()
    {
        return $this->matrix;
    }

    protected static function nonzero_rand()
    {
        return (mt_rand(1, 2) === 1) ? 1 : -1;
    }

    /**
     * 活性化関数
     *
     * @param  mixed $val 各状態を表す数値
     * @return int
     */
    protected abstract function activate($val);


    protected abstract function defferential($val);

}
