<?php
namespace backend\widgets;

use common\models\Lead;

class LeadWidget extends \yii\base\Widget
{
    public function run()
    {

        $unreadLeadsCount = Lead::find()->where(['status'=>Lead::UNREAD_LEAD])->count();
        return $this->render('leads', compact('unreadLeadsCount'));
    }
}