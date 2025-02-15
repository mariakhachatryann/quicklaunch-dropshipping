<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 01.07.2019
 * Time: 15:16
 */

namespace frontend\widgets;


use common\models\Category;
use yii\base\Widget;

class InstructionSteps extends Widget
{
    public $sites;
    public function run()
    {
        $sites = $this->sites;
        $firstFaq = Category::find()->limit(1)->one();
        $firstFaqId = $firstFaq ? $firstFaq->id : 0;
        return $this->render('instruction-steps', compact('sites', 'firstFaqId'));
    }
}