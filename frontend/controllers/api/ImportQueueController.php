<?php

namespace frontend\controllers\api;

use common\models\AlertCaptcha;
use common\models\ImportQueue;
use common\models\ProductUrl;
use Yii;
use yii\base\InvalidArgumentException;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;

class ImportQueueController extends ActiveController
{
    public $modelClass = ImportQueue::class;

    public $privateApiKey = '9e56f9d3aff72b9540472275aa60b1382c9e4e87';


    public function actionIndex(int $v = null, int $h = null, $country = null,  $type = 0)
    {

        if (!$country || $country == 'null') {
            $countries = [ImportQueue::COUNTRY_US];
        } else {
            $countries = explode(',', $country);
        }

        if (!$v || $v < ImportQueue::VERSION) {
            return null;
        }

        if ($country == ImportQueue::COUNTRY_ANY) {
            $countries = null;
        }

        $type = $type == ImportQueue::TYPE_IMPORT ? $type : ImportQueue::TYPE_MONITORING;

        /* @var ImportQueue $importQueue*/
        $importQueue = ImportQueue::find()->andFilterWhere([
            'status' => ImportQueue::STATUS_PENDING,
            'country' => $countries,
            'type' => $type
        ])->limit(1)->one();
        if ($importQueue) {
            $userLimits = $importQueue->user->getLimits();
            $reviewLimit = $userLimits['reviewsRemainsLimit'] ?? 0;
            $importQueue->status = ImportQueue::STATUS_PROCESSING;
            $importQueue->processing_ip = Yii::$app->request->userIP;
            $importQueue->handler = $h;
            $importQueue->save();
            return ['queue'  => $importQueue, 'reviewLimit' => $reviewLimit, 'import_reviews' => $importQueue->import_reviews];
        }
    }

    public function actionUpdate(int $id)
    {
        $importQueue = ImportQueue::findOne($id);
        $content = Yii::$app->request->post('content');
        $importQueue->updateData($content);
        if ($content && isset($content['status']) && !$content['status']) {
            $importQueue->status = ImportQueue::STATUS_ERROR;
        } else {
            $importQueue->status = ImportQueue::STATUS_SUCCESSFUL;
        }

        $importQueue->save(false);
        return [
            'success' => true
        ];
    }
    
    public function actionAlertCaptcha()
    {
        $id = Yii::$app->request->post('id');
        $importQueue = ImportQueue::findOne($id);
        if (!$importQueue) {
            throw new NotFoundHttpException($id . ' Import queue not found!');
        }
        Yii::error([
            'id' => $importQueue->id,
            'url' => $importQueue->url,
            'handler' => $importQueue->handler,
            'country' => ImportQueue::COUNTRY_MAP[$importQueue->country] ?? $importQueue->country,
        ], 'Captcha');

        $alertCaptcha = new AlertCaptcha();
        $alertCaptcha->import_queue_id = $importQueue->id;
        $alertCaptcha->handler = $importQueue->handler;
        $alertCaptcha->status = AlertCaptcha::STATUS_PENDING;

        if ($alertCaptcha->validate()) {
            $alertCaptcha->save();
        }
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['update']);
        unset($actions['index']);
        return $actions;
    }

    public function actionGetDataFromContent()
    {

        $model = new ProductUrl();
        if ($model->load(Yii::$app->request->post(), '') && $model->validate() && Yii::$app->request->post('token') == $this->privateApiKey) {
            if (!$model->content) {
                throw new InvalidArgumentException('Content is missing!');
            }
            $site = $model->getSite();

            if (!$site) {
                throw new NotFoundHttpException('Invalid product url!');
            }

            $model->url = str_replace('-&-', '', $model->url);
            $productData = $model->getProductData();

            $productData = json_decode($productData, true);
            return $productData;
        } else {
            return $model->getErrors();
        }

    }
}