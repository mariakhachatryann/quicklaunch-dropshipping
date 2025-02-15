<?php


namespace frontend\models\api;


use yii\helpers\Url;

class Notification extends \common\models\Notification
{
    public function fields()
    {
        $fields = parent::fields();
        $fields['created_at'] = 'createdAt';
        $fields['notificationUrl'] = 'notificationUrl';
        return $fields;
    }

    public function getCreatedAt()
    {
        return $this->created_at ? date(\common\models\Product::DATE_DISPLAY_FORMAT, $this->created_at) : null;
    }

    public function getNotificationUrl()
    {
        return $this->url ? $this->url : '/notifications';
    }

}