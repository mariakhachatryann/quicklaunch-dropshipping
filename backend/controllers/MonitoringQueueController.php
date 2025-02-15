<?php

namespace backend\controllers;

use backend\models\Admin;
use common\models\Product;
use Yii;
use common\models\MonitoringQueue;
use backend\models\MonitoringQueueSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MonitoringQueueController implements the CRUD actions for MonitoringQueue model.
 */
class MonitoringQueueController extends AdminController
{
    protected $allowedRoles = [Admin::ROLE_CAPTCHA_SOLVER];
    /**
     * Lists all MonitoringQueue models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MonitoringQueueSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $totalCount = Product::getMonitoringProductsQuery()->count();

        return $this->render('index', compact('searchModel', 'dataProvider', 'totalCount'));
    }

    /**
     * Displays a single MonitoringQueue model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Deletes an existing MonitoringQueue model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the MonitoringQueue model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MonitoringQueue the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MonitoringQueue::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
