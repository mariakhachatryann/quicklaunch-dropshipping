<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 24.08.2019
 * Time: 12:17
 */

namespace frontend\widgets;

use common\models\Product;
use common\models\User;
use yii\base\Widget;

class GraphicWidget extends Widget
{
    public function run()
    {
        return $this->render('graphic');
    }

}