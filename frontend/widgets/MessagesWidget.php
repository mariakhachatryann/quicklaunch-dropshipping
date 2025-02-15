<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 20.08.2019
 * Time: 10:56
 */

namespace frontend\widgets;


use yii\base\Widget;

class MessagesWidget extends Widget
{
    public $directoryAsset;

    public function run()
    {
        $directoryAsset = $this->directoryAsset;
        return $this->render('messages', compact('directoryAsset'));
    }
}