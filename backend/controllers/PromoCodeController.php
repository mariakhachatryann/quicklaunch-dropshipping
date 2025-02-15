<?php

namespace backend\controllers;

use common\models\Plan;
use Yii;
use common\models\PromoCode;
use backend\models\PromoCodeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PromoCodeController implements the CRUD actions for PromoCode model.
 */
class PromoCodeController extends AdminController
{

    /**
     * Lists all PromoCode models.
     * @return mixed
     */
    public function actionIndex()
    {

        $searchModel = new PromoCodeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PromoCode model.
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
     * Creates a new PromoCode model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PromoCode();
        /* @var $model PromoCode */
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->active_until = strtotime($model->active_until.' 23:59:59');
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', compact('model'));
    }

    /**
     * Updates an existing PromoCode model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        /* @var $model PromoCode */
        $model->userName = $model->user->username;
        $model->active_until = date('Y-m-d', $model->active_until);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->active_until = strtotime($model->active_until.' 23:59:59');
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('update', compact('model'));
    }

    /**
     * Deletes an existing PromoCode model.
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
     * Finds the PromoCode model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PromoCode the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PromoCode::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
