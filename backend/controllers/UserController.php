<?php

namespace backend\controllers;

use backend\models\Admin;
use Yii;
use common\models\User;
use backend\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends AdminController
{
    protected $allowedRoles = [Admin::ROLE_SUPPORT, Admin::ROLE_CAPTCHA_SOLVER];

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (Yii::$app->user->identity->role_type == Admin::ROLE_CAPTCHA_SOLVER) {
            return $this->redirect(['/failed-queue/index']);
        }
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        try {
            $model->setOrUpdateUserCharges();
        } catch (\Throwable $exception) {

        }
        return $this->render('view', compact('model'));
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $cookies = Yii::$app->request->cookies;
            $language = $cookies->getValue('language', 'en');
            $model->setPassword($model->password);
            $model->auth_key = Yii::$app->security->generateRandomString();
            $model->access_token = Yii::$app->security->generateRandomString();
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $session = Yii::$app->session;
        $session->setFlash('anyKey', 'You have successfully entered to update.');
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
			$model->left_review_at = strtotime($model->left_review_at);
			if ($model->save()) {
				return $this->redirect(['view', 'id' => $model->id]);
			}
        }

		$model->left_review_at = date('Y-m-d', $model->left_review_at);
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing User model.
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
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

   public function actionInactive($id)
   {
       $user = User::findOne($id);
       $user->inactiveUserPlan();
       return $this->redirect(['index']);
   }

    public function actionSetBasicPlan(int $id)
   {
       $user = $this->findModel($id);
       $user->setBasicPlan();
       return $this->redirect(['view', 'id' => $id]);
   }

}
