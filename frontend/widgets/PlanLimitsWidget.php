<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 04.07.2019
 * Time: 17:52
 */

namespace frontend\widgets;


use common\models\Plan;
use common\models\User;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

class PlanLimitsWidget extends Widget
{
    public function run()
    {
        $user = \Yii::$app->user->identity;
        /* @var $user User*/
        if ($user->plan_status == Plan::PLAN_INACTIVE || !$user->plan) {
            return '';
        }
        $limits = $user->getLimits();
        $productLimit = $limits['productLimit'];
        $productCount = $limits['productCount'];
        $productPercent = $limits['productPercent'];

        $monitoringLimit = $limits['monitoringLimit'];
        $monitoringCount = $limits['monitoringCount'];
        $monitoringPercent = $limits['monitoringPercent'];

        $reviewsLimit = $limits['reviewsLimit'];
        $reviewsCount = $limits['reviewsCount'];
        $reviewsPercent = $limits['reviewsPercent'];

        return $this->render('plan-limits', compact('productPercent', 'variantsPercent',
            'reviewsPercent', 'productLimit', 'productCount', 'variantsLimit', 'variantsCount', 'reviewsLimit','reviewsCount'));
    }



}