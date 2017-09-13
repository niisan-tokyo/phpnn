<?php

namespace Niisan\phpnn\layer;

/**
 * NNを構成する各レイヤーのベースクラス
 *
 *
 */
abstract class Base
{
    protected $input_dim = 0;
    protected $output_dim = 0;
    protected $matrix = [];
    protected $effect = 0.01;

    private $state_history = [];
    private $input_history = [];
    private $history_count = 1;
    private $dropout = 0;
    private $is_dropout = true;
    private $dropout_history = [];
    private $delta_matrix = [];

    public static function createInstance()
    {
        return new static;
    }

    public function __construct($output_dim)
    {
        $this->output_dim = $output_dim;
    }

    /**
     * レイヤーを初期化する
     *
     * 初期化は、与えられたオプションをパラメータに適用し、
     * 重みをランダムな値で初期化する
     *
     * @param  int    $input_dim 入力次元数
     * @param  array  $option    オプション値
     *
     * @return static            自分自身を返す
     */
    public function init($input_dim, $option = [])
    {
        echo "$input_dim, $this->output_dim \n";
        $this->input_dim = $input_dim;
        for ($i = 0; $i < $this->output_dim; $i++) {
            for ($j = 0; $j < $input_dim; $j++) {
                $this->matrix[$i][$j] = self::nonzero_rand() / $input_dim;
            }
        }

        foreach ($option as $key => $val) {
            $this->{$key} = $val;
        }

        return $this;
    }

    /**
     * 入力パラメータを用いて次のパラメータセットを出力する
     *
     * ドロップアウトが設定されていると、ランダムで設定された割合だけ
     * 出力パラメータをゼロにする
     *
     * @param  array $states 入力パラメータ
     *
     * @return array         出力パラメータ
     */
    public function prop($states)
    {
        $ret = [];
        $dropout = $this->dropout();

        // propergate
        for ($i = 0; $i < $this->output_dim; $i++) {
            $temp = 0;

            for ($j = 0; $j < $this->input_dim; $j++) {
                $temp += $this->matrix[$i][$j] * $states[$j];
            }

            // if dropout exists, activation is 0.
            $ret[$i] = $this->activate($temp) * $dropout[$i];
            $param[$i] = $temp;
        }

        // memorize this states
        $this->state_history = $states;
        $this->input_history = $param;
        $this->dropout_history = $dropout;

        return $ret;
    }

    /**
     * 逆伝播を行い、重み行列の更新を行う
     *
     * dropoutで設定された項目は変更の対象外になる
     *
     * @param  array $states 次の層の逆伝播状態
     *
     * @return array         逆伝播出力
     */
    public function backProp($states)
    {
        $ret = array_fill(0, $this->input_dim, 0);
        $back_state = $this->state_history;
        $back_input = $this->input_history;
        $dropout = $this->dropout_history;
        $delta = [];
        for ($i = 0; $i < $this->output_dim; $i++) {
            $delta[$i] = $this->defferential($back_input[$i]) * $states[$i] * $dropout[$i];
            for ($j = 0; $j < $this->input_dim; $j++) {
                $ret[$j] += $this->matrix[$i][$j] * $delta[$i];
                $this->delta_matrix[$i][$j] -= $this->effect * $delta[$i] * $back_state[$j];
            }
        }

        return $ret;
    }

    /**
     * 蓄積された更新差分を個々で適用する
     * 
     * @return [type] [description]
     */
    public function correct()
    {
        foreach ($this->delta_matrix as $index => $record) {
            foreach ($record as $key => $val) {
                $this->matrix[$index][$key] += $val;
            }
        }

        $this->delta_matrix = [];
    }

    /**
     * 内部状態をリセットする
     */
    public function reset()
    {
        $this->state_history = [];
        $this->input_history = [];
        $this->dropout_history = [];
    }

    /**
     * 現在のレイヤーの状態をファイルに書き出す
     *
     * @param  string $file ファイル名
     */
    public function save($file)
    {
        //print_r($this->matrix);
        file_put_contents($file, serialize($this->matrix));
    }

    /**
     * ファイルを読み込んで、重み状態を復元する
     *
     * @param  string $file ファイル名
     */
    public function load($file)
    {
        $str = file_get_contents($file);
        $this->matrix = unserialize($str);
    }


    public function export()
    {
        return $this->matrix;
    }

    public function getOutputDim()
    {
        return $this->output_dim;
    }

    public function getMatrix()
    {
        return $this->matrix;
    }

    public function setEffect($effect)
    {
        $this->effect = $effect;
    }

    public function isDropOut()
    {
        return $this->is_dropout;
    }

    public function switchDrop()
    {
        $this->is_dropout = ! $this->is_dropout;
    }

    protected static function nonzero_rand()
    {
        return (mt_rand(1, 2) === 1) ? 1 : -1;
    }

    protected function dropout()
    {
        $vec = array_fill(0, $this->output_dim, 1);
        if ($this->dropout != 0 and $this->is_dropout) {
            $num = floor($this->output_dim * $this->dropout);
            if ($num > 0) {
                $keys = array_rand($vec, $num);
                foreach ($keys as $val) {
                    $vec[$val] = 0;
                }
            }
        }
        return $vec;
    }

    public function __sleep()
    {
        return [
            'input_dim', 'output_dim', 'matrix', 'effect'
        ];
    }

    public function __wakeup(){}

    /**
     * 活性化関数
     *
     * @param  mixed $val 各状態を表す数値
     * @return int
     */
    protected abstract function activate($val);


    protected abstract function defferential($val);

}
