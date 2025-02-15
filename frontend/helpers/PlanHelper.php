<?php

namespace frontend\helpers;

use common\models\Plan;
use common\models\User;
use Yii;

class PlanHelper
{

    public static function subscribeUserToFreePlan(User $user)
    {
        $plan = Plan::findOne(['price' => 0]);
        if ($plan) {
            $user->subscribe($plan);
        }
    }

}