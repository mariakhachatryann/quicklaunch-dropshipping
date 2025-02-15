<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 16.04.2019
 * Time: 10:44
 */

namespace frontend\controllers\api;


use common\models\LoginForm;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;
use yii\rest\Controller;
use yii\web\Response;

class ApiController extends Controller
{

    protected $loginRequired = true;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        if ($this->loginRequired) {
            $behaviors['authenticator'] = [
                'class' => HttpBearerAuth::class,
                'header' => 'Auth',
            ];
            $behaviors['authenticator']['except'] = ['options'];
        }
        $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_JSON;
        return array_merge($behaviors, [
            // For cross-domain AJAX request
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
                'cors' => [
                    // restrict access to domains:
                    'Origin' => Yii::$app->params['allowedDomains'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Max-Age' => 86400,
                    'Access-Control-Expose-Headers' => [],
                ],
            ],

        ]);
    }

    public function init()
    {
        \Yii::$app->user->enableSession = false;
    }

    public function actions()
    {
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS'],
            'view' => ['GET', 'HEAD', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['PUT', 'PATCH', 'OPTIONS'],
            'delete' => ['DELETE', 'OPTIONS'],
        ];
    }
}