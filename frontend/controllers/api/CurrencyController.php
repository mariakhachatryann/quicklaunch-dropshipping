<?php


namespace frontend\controllers\api;


use backend\helpers\CurrencyHelper;
use common\models\Currency;
use Yii;
use yii\helpers\ArrayHelper;

class CurrencyController extends ApiController
{

    public function actionIndex()
    {
        return ArrayHelper::toArray(
            Currency::getDb()->cache(function ($db) {
                return Currency::find()->all();
            }, 1000)
        );
    }

    public function actionConvert()
    {
        $from = Currency::getDb()->cache(function ($db) {
                return Currency::findOne(['id' => Yii::$app->request->post('from')]);
            }, 1000);
        $to = Currency::getDb()->cache(function ($db) {
                return Currency::findOne(['id' => Yii::$app->request->post('to')]);
            }, 1000);

        return CurrencyHelper::convert($from->code, $to->code);
    }

}