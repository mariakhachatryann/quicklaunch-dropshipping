<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 18.01.2020
 * Time: 12:23
 */

namespace frontend\widgets;

use yii\base\Widget;

class RateWidget extends Widget
{
    
    public $rate;
    
    public function run()
    {
        return $this->render('rate', ['rate' => $this->rate]);
    }

}