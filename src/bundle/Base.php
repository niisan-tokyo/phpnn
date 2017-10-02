<?php
namespace Niisan\phpnn\bundle;

use ProgressBar\Manager as Progress;

/**
 * ニューラルネットワーク全体を定義するクラス
 */
abstract class Base
{
    protected $bundle = [];
    private $value = [];
    private $epoch = 0;

    private $last_dimension;
    private $batch_size = 1;
    private $batch_count = 0;

    protected $loss_val = 0;

    /**
     * ネットワークに新しい層を追加する
     *
     * 最初に追加する層の場合は、必ず$optionにinput_dimを設定しておくこと
     * $optionの値は追加したそうの初期化に使用される
     *
     * @param object $obj    層を表すオブジェクト
     * @param array  $option 層の初期化に試用するパラメータ
     */
    public function add($obj, $option = [])
    {
        $this->bundle[] = $obj;

        $input_dim = $this->last_dimension ?? $option['input_dim'];
        $obj->init($input_dim, $option);
        $this->last_dimension = $obj->getOutputDim();
    }

    /**
     * 学習を実施する
     *
     * トレーニングデータ$trainの第１要素が入力、
     * 第２要素が出力されるべきデータとなっている
     * 指定できるオプションは以下の通り
     *
     * epoch            : 学習の繰り返し回数
     * batch_size       : バッチサイズ。何個分のデータを使用して重みの修正を行うか
     * effect           : 学習率。
     * test             : テスト用のデータ。これが存在すると、各epoch後にテストを実施する
     * test_function    : テストに試用する関数名
     * accuracy_callback: テストデータに対し、予測がどの程度正しいかを測るためのコールバック関数を登録する
     *
     * @param  array $train  訓練データ
     * @param  array $option オプション
     *
     * @return void
     */
    public function fit($train, $option)
    {
        $X = $train[0];
        $Y = $train[1];
        $epoch = $option['epoch'] ?? 1;
        $this->batch_size = $option['batch_size'] ?? 1;
        if (isset($option['effect'])) {
            $this->setEffect($option['effect']);
        }
        for ($i = 1; $i < $epoch + 1; $i++) {
            echo "$i / $epoch \n";
            $prog = new Progress(0, count($X));
            $keys = array_keys($X);
            shuffle($keys);
            foreach ($keys as $key) {
                $this->exec($X[$key]);
                $this->correct($Y[$key]);
                $prog->advance();
            }
            $this->correctMatrix();

            if (isset($option['test'])) {
                $method = $option['test_function'] ?? 'loss';
                $acc_callback = $option['accuracy_callback'] ?? null;
                $str = $this->{'test' . ucfirst($method)}($option['test']);
                $str .= $this->check_accuracy($option['test'], $acc_callback);
                echo $str . "\n";
            }

        }

    }

    abstract public function exec($value);

    abstract public function correct($value);

    /**
     * 順伝播
     *
     * 関数として入力に対して出力を返す
     *
     * @param  mixed $state 配列、もしくは単体の入力値
     *
     * @return array        出力値
     */
    protected function foreLoop($state)
    {
        if (!is_array($state)) {
            $state = [$state];
        }

        $ret = $state;
        foreach ($this->bundle as $obj) {
            $ret = $obj->prop($ret);
        }

        $this->value = $ret;
        return $ret;
    }

    /**
     * 誤差逆伝播
     *
     * 誤差逆伝播法に従い、foreLoopで出力された値と本来出力されるべき値を比較し、
     * 各層の重み配列の修正を蓄積させる
     * 蓄積回数が$batch_sizeに等しくなると、実際に各層への重み修正を実行する
     *
     * @param  mixed $state 本来出力されるべき値
     * @return void
     */
    protected function backLoop($state)
    {
        $this->batch_count++;
        $diff = [];
        $state = (is_array($state)) ? $state : [$state];
        $diff = $this->lossDiff($state);

        $ref = count($this->bundle) - 1;
        for ($i = $ref; $i >= 0; $i--) {
            $diff = $this->bundle[$i]->backProp($diff);
        }

        if ($this->batch_count == $this->batch_size) {
            $this->correctMatrix();
            $this->batch_count = 0;
        }

    }

    /**
     * 蓄積された各層の重み修正分を適用する
     *
     * @return void
     */
    protected function correctMatrix()
    {
        foreach ($this->bundle as $layer) {
            $layer->correct();
        }
    }

    protected function lossDiff($state)
    {
        $ret = [];
        foreach ($state as $key => $val) {
            $ret[$key] = ($this->value[$key] - $val) / $this->batch_size;
        }

        return $ret;
    }

    protected function loss($state)
    {
        $loss = 0;
        $state = (is_array($state)) ? $state : [$state];
        foreach ($state as $key => $val) {
            $loss += ($this->value[$key] - $val) ** 2 / 2;
        }

        return $loss;
    }

    protected function dropSwitch()
    {
        foreach ($this->bundle as $layer) {
            $layer->switchDrop();
        }
    }

    protected function testLoss($test_data)
    {
        $X = $test_data[0];
        $Y = $test_data[1];
        //print_r($test_data);
        $loss = 0;
        foreach ($X as $key => $val) {
            $res = $this->exec($val);
            //print_r($res);
            $loss += $this->loss($Y[$key]);
        }

        echo "loss: $loss ";
    }

    /**
     * テストデータを使って、予測の正確さを計測する
     *
     * コールバック関数に、予測の正誤判定ロジックを
     * クロージャで指定する
     *
     * @param  array    $test_data    [description]
     * @param  callable $acc_callback [description]
     *
     * @return string
     */
    protected function check_accuracy($test_data, callable $acc_callback = null): string
    {
        if ($acc_callback === null) {
            return '';
        }

        $X = $test_data[0];
        $Y = $test_data[1];
        $sum = $acc = 0;
        foreach ($X as $key => $val) {
            $sum++;
            $res = $this->exec($val);
            if ($acc_callback($res, $Y[$key])) {
                $acc++;
            }
        }

        $ret = round($acc * 100 / $sum, 1);
        return "accuracy: $ret % ";
    }

    private function setEffect($effect)
    {
        foreach ($this->bundle as $layer) {
            $layer->setEffect($effect);
        }
    }

    /**
     * 現在のモデルの状況を保存する
     *
     * @param  string $file ファイル名
     */
    public function save(string $file)
    {
        $str = serialize($this);
        file_put_contents($file, $str);
    }

    /**
     * モデルをロードする
     *
     * @param  string $file ファイル名
     *
     * @return static       モデルのインスタンスを返却する
     */
    public static function load(string $file)
    {
        $data = file_get_contents($file);
        return unserialize($data);
    }

    public function __sleep()
    {
        return ['bundle'];
    }

    public function __wakeup(){}
}
