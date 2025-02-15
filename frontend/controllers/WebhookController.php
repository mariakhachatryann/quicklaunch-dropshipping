<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 15.05.2019
 * Time: 15:16
 */

namespace frontend\controllers;

use common\models\Product;
use yii\web\Controller;
use Yii;
use common\models\User;
use common\models\Plan;
use common\models\PlanChargeRequest;
use yii\web\NotFoundHttpException;

class WebhookController extends Controller
{
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if (!User::verifyWebhook() && !in_array($this->action->id,['customers-redact','shop-redact', 'customers-data-request'])) {

                throw new NotFoundHttpException();
            }
            return true;
        }
        return false;
    }

    public $enableCsrfValidation = false;

    // Weebhook uninstall that will delete all charges and plans of shop
    public function actionUninstall()
    {
        $domain = Yii::$app->request->getBodyParam('domain');

        if (!$domain) {
            throw new NotFoundHttpException();
        }
        $username = explode('.', $domain)[0];
        $shop = User::findOne(['username' => $username]);
        /* @var $shop User*/
        if ($shop && $shop->plan_status != Plan::PLAN_INACTIVE) {
            $shop->status = User::STATUS_DELETED;
            $shop->plan_id = null;
            $shop->plan_status = Plan::PLAN_INACTIVE;
            PlanChargeRequest::deleteAll(['user_id' => $shop->id]);
            if ($shop->save()){
                $shop->sendEmail(
                    Yii::$app->params['supportEmail'],
                    'Uninstallation',
                    $shop->username.' has uninstalled application');
            }
        }
    }

    public function actionProductDelete()
    {
        /* @var $user User*/
        $id = Yii::$app->request->getBodyParam('id');
        $product = Product::findOne(['shopify_id' => $id]);
        /* @var $product Product*/
        if ($product) {
            $product->is_published = 0;
            $product->shopify_id = null;
            $product->handle = null;
            $product->save();
            return 1;
        }

        return false;
    }
    public function actionCustomersRedact()
    {
        return true;
    }

    public function actionShopRedact()
    {
        return true;
    }

    public function actionCustomersDataRequest()
    {
        return true;
    }


    public function actionReviews()
    {
        $outp = ['key' => 'value'];
        return "reviewsCallback(".json_encode($outp).")";
    }
}