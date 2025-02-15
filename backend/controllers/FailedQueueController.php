<?php


namespace backend\controllers;


use backend\models\Admin;
use backend\models\AlertCaptchaSearch;
use backend\models\CaptchaSolverLog;
use backend\models\ImportQueueSearch;
use common\models\AlertCaptcha;
use common\models\ImportQueue;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class FailedQueueController extends AdminController
{

    protected $allowedRoles = [Admin::ROLE_CAPTCHA_SOLVER];

    public function actionIndex()
    {
        $searchModel = new ImportQueueSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['>', 'created_at', strtotime('-2 hours')])
            ->andWhere(['status' => ImportQueue::STATUS_ERROR]);
        $alertCaptchaSearchModel = new AlertCaptchaSearch();
        $alertCaptchaDataProvider = $alertCaptchaSearchModel->search(Yii::$app->request->queryParams);
        $alertCaptchaDataProvider->query->andWhere(['>', AlertCaptcha::tableName() . '.created_at', strtotime('-15 minutes')]);


        return $this->render('index', compact('dataProvider', 'searchModel', 'alertCaptchaSearchModel', 'alertCaptchaDataProvider'));
    }

    /**
     * Handles the AJAX request to toggle the "is_online" status.
     *
     * @return array
     */
    public function actionToggleOnlineStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->request->isPost) {
            $isOnline = Yii::$app->request->post('is_online');
            $admin = Yii::$app->user->identity;

            $admin->is_online = $isOnline;

            if ($admin->save()) {
                if ($isOnline) {
                    $log = new CaptchaSolverLog();
                    $log->admin_id = $admin->id;
                } else {
                    $log = CaptchaSolverLog::find()->where(['admin_id' => $admin->id])->orderBy(['activated_at' => SORT_DESC])->one();
                    $log->deactivated_at = time();
                }

                if ($log->save()) {
                    $statusMessage = $isOnline ? 'Status updated to online. New log created.' : 'Status updated to offline. Log updated.';
                    return ['success' => true, 'message' => $statusMessage];
                }
            } else {
                return ['success' => false, 'message' => 'Failed to save the status.'];
            }
        }

        return ['success' => false, 'message' => 'Invalid request.'];
    }



}