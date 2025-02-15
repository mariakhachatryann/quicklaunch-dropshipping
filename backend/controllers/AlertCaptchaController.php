<?php

namespace backend\controllers;

use backend\models\Admin;
use Yii;
use common\models\AlertCaptcha;
use backend\models\AlertCaptchaSearch;
use yii\data\Sort;
use yii\db\Exception;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * AlertCaptchaController implements the CRUD actions for AlertCaptcha model.
 */
class AlertCaptchaController extends AdminController
{
    protected $allowedRoles = [Admin::ROLE_CAPTCHA_SOLVER];
    /**
     * Lists all AlertCaptcha models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AlertCaptchaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->orderBy(['id' => SORT_DESC]);
        $captchaSolvers = Admin::find()->where(['role_type' => Admin::ROLE_CAPTCHA_SOLVER])->all();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'captchaSolvers' => $captchaSolvers,
        ]);
    }

    /**
     * Displays a single AlertCaptcha model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $captcha = $this->findModel($id);

        return $this->render('view', [
            'model' => $captcha,
        ]);
    }

    /**
     * Creates a new AlertCaptcha model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new AlertCaptcha();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AlertCaptcha model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing AlertCaptcha model.
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
     * Finds the AlertCaptcha model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return AlertCaptcha the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AlertCaptcha::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionSolveCaptcha($id)
    {
        $captcha = $this->findModel($id);

        if ($captcha->solve()) {
            return $this->redirect(Url::toRoute(['failed-queue/index']));
        } else {
            Yii::$app->session->setFlash('error', 'Captcha is expired');
            return $this->redirect(Url::toRoute(['failed-queue/index']));
        }
    }

    public function actionCancelCaptcha($id)
    {
        $captcha = $this->findModel($id);
        $captcha->status = AlertCaptcha::STATUS_CANCELLED;
        $captcha->save();

        return $this->redirect(Url::toRoute(['failed-queue/index']));
    }

    public function actionTake($id)
    {
        $captcha = $this->findModel($id);

        if ($captcha->status == AlertCaptcha::STATUS_PENDING) {
            $captcha->status = AlertCaptcha::STATUS_TAKEN;
            $captcha->admin_id = Yii::$app->user->id;
            $captcha->taken_at = time();

            if ($captcha->save()) {
                return $this->redirect(['view', 'id' => $captcha->id]);
            }
        } else {
            Yii::$app->session->setFlash('error', 'This captcha has already been taken.');
        }

        return $this->redirect(Url::toRoute(['failed-queue/index']));
    }

}
