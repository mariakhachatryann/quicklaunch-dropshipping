<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 05.07.2019
 * Time: 13:04
 */

namespace frontend\widgets;


use common\models\Lead;
use common\models\User;
use yii\base\Widget;

class TicketsWidget extends Widget
{
    public function run()
    {
        $user = \Yii::$app->user->identity;
        /* @var $user User*/
        if (!$user) {
            return '';
        }
        $tickets = $user->getLeads()->where(['!=', 'status', Lead::CLOSED])->orderBy(['id' => SORT_DESC])->all();

        return $this->render('tickets', compact('tickets'));
    }

}