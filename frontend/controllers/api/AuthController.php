<?php

namespace frontend\controllers\api;

use common\helpers\ShopifyClient;
use common\models\Plan;
use common\models\User;
use common\models\UserSetting;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;


class AuthController extends Controller
{

    public function behaviors()
    {
        $behavours = parent::behaviors();
        return array_merge($behavours, [


            // For cross-domain AJAX request
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
                'cors' => [
                    // restrict access to domains:
                    'Origin' => Yii::$app->params['allowedDomains'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Allow-Origin' => '*',
                    //'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Max-Age' => 86400,
                    'Access-Control-Expose-Headers' => [],             // Cache (seconds)
                ],
            ],

        ]);
    }

    public function actions()
    {
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }

    public function actionLoginApp()
    {
        $shop = Yii::$app->request->post('shop');
        return User::redirectLoginUrl($shop);
    }

    public function actionGetAccessToken()
    {
        $code = Yii::$app->request->post('code');
        $shop = Yii::$app->request->post('shop');
        return User::getAccessToken($shop, $code);
    }

    /**
     * {@inheritdoc}
     */
    protected function verbs()
    {
        return [
            'login-app' => ['POST', 'OPTIONS'],
            'get-access-token' => ['POST', 'OPTIONS'],
            'test' => ['POST'],
        ];
    }


}