<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 18.01.2020
 * Time: 12:23
 */

namespace frontend\widgets;


use common\models\Category;
use common\models\User;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

class LimitPillsWidget extends Widget
{
    public  $productsCount;

    public function run()
    {
        $user = Yii::$app->user->identity;
        /* @var User $user*/
        $productsCount = $this->productsCount;

        $productsLimit = ArrayHelper::getValue($user,'plan.product_limit');
        $monitoringLimit = ArrayHelper::getValue($user,'plan.monitoring_limit');
        if ($user->email == 'nik5iron@gmail.com') {
            $monitoringLimit = 1500;
        }
        $reviewsLimit = ArrayHelper::getValue($user,'plan.review_limit');

        $monitoringCount = $user->getMonitoringProducts()->count();
        $reviews = $user->getProductsReviews()->count();
        return $this->render('limit_pills', compact('productsCount','monitoringCount', 'reviews',
            'productsLimit', 'monitoringLimit', 'reviewsLimit'));
    }

}