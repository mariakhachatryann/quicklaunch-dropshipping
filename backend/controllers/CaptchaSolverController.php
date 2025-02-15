<?php

namespace backend\controllers;

use backend\models\Admin;
use common\models\AlertCaptcha;
use common\models\CaptchaSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;


class CaptchaSolverController extends AdminController
{
    protected $allowedRoles = [Admin::ROLE_CAPTCHA_SOLVER];
    public function actionIndex()
    {
        $this->layout = 'main';

        $searchModel = new CaptchaSearch();

        $query = Admin::find()->where(['role_type' => Admin::ROLE_CAPTCHA_SOLVER]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }




    public function actionView($id)
    {
        $this->layout = 'main';
        $solver = Admin::findOne($id);
        $searchModel = new CaptchaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['admin_id' => $solver->id]);
        $dailyAverage = AlertCaptcha::getSolvingDurationForDay($solver->id);
        $monthlyAverage = AlertCaptcha::getSolvingDurationForMonth($solver->id);

        $dailyChartData = AlertCaptcha::getDailyChartData($solver->id);
        $monthlyChartData = AlertCaptcha::getMonthlyChartData($solver->id);

        return $this->render('view', [
            'solver' => $solver,
            'searchModel' => $searchModel,
            'dailyAverage' => $dailyAverage,
            'monthlyAverage' => $monthlyAverage,
            'dailyChartData' => $dailyChartData,
            'monthlyChartData' => $monthlyChartData,
            'dataProvider' => $dataProvider
        ]);
    }


}
