<?php

namespace frontend\widgets;

use common\models\User;
use yii\base\Widget;
use Yii;

class NotificationsWidget extends Widget
{
    public function run()
    {
        $user = Yii::$app->user->identity;
        /* @var $user User*/
        if (!$user) {
            return '';
        }

        $notifications = $user->lastTenNotifications();

        $countNewNotifications = count($user->getNewNotificationIds());
        $countNotifications = count($user->lastTenNotifications());
        $newNotificationIds = $user->getNewNotificationIds();

        return $this->render('notifications', compact('notifications',
            'countNewNotifications',
            'countNotifications',
            'newNotificationIds'
        ));
    }

}