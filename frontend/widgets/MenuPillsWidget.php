<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 18.01.2020
 * Time: 12:23
 */

namespace frontend\widgets;


use common\models\Category;
use yii\base\Widget;

class MenuPillsWidget extends Widget
{
    public function run()
    {

        $firstFaq = Category::find()->limit(1)->one();
        $firstFaqId = $firstFaq ? $firstFaq->id : 0;
        return $this->render('menu_pills', compact('firstFaqId'));
    }

}