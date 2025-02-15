<?php

namespace frontend\controllers;

use common\models\Product;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

class UserController extends Controller
{

    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $user = Yii::$app->user->identity;
            /* @var User $user*/
            if (time() - $user->updated_at > 600) {
                $user->updated_at = time();
                $user->save();
            }
            Yii::$app->view->params['switchMenu'] = Yii::$app->request->cookies->getValue(ProductController::COOKIE_DISPLAY_TYPE) != Product::DISPLAY_lIST_STYLE;
            Yii::$app->view->params['switchMenu'] = false;

            return true;
        }
        return false;
    }

    public function actionProductPricingRules()
    {
      $user = Yii::$app->user->identity;
      return json_encode(ArrayHelper::toArray($user->productPricingRules));

    }
}