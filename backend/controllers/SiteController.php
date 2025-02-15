<?php
namespace backend\controllers;

use backend\models\Admin;
use common\models\Lead;
use common\models\Product;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\models\LoginForm;
use common\models\ProductReview;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index','login-user'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->redirect(['/user/index']);
        //return $this->render('index');
    }

    /**
     * Login action.
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
			if (Yii::$app->user->identity->role_type == Admin::ROLE_SUPPORT) {
				return $this->redirect(['/lead/index']);
			} elseif (Yii::$app->user->identity->role_type == Admin::ROLE_CAPTCHA_SOLVER) {
                return $this->redirect(['/failed-queue/index']);
            }
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }
    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionLoginUser($userId){

        $token = Yii::$app->security->generateRandomString();
        Yii::$app->cache->set('loginToken',$token,30);

        return $this->redirect('/site/user-login?userId='.$userId.'&token='.$token);
    }
}
