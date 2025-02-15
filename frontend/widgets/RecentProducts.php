<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 29.06.2019
 * Time: 11:42
 */

namespace frontend\widgets;


use common\models\Product;
use Yii;
use yii\base\Widget;

class RecentProducts extends Widget
{

    public function run()
    {

        $products = Product::find()
            ->andWhere([
                'user_id' => Yii::$app->user->id,
                'is_deleted' => Product::PRODUCT_IS_NOT_DELETED
            ])
            ->limit(6)
            ->orderBy(['id' => SORT_DESC])
            ->all();
        return $this->render('recent-products', compact('products'));
    }


}