<?php


namespace backend\controllers;


use backend\models\Credit;
use backend\models\CreditSearch;
use common\models\Plan;
use Yii;
use yii\base\BaseObject;
use yii\web\NotFoundHttpException;

/**
 * CreditController implements the CRUD actions for Credit model.
 */

class CreditController extends AdminController
{

    /**
     * Lists all Credit models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CreditSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Credit model.
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
     * Creates a new Credit model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($userId)
    {
        $model = new Credit();

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $model->amount = $model->plan->price;
                try {
                    $creditId = $model->createCredit();
                    $model->status = Credit::STATUS_SUCCESS;
                    $model->shopify_credit_id = $creditId;
                } catch (\Throwable $error) {
                    $model->status = Credit::STATUS_ERROR;
                    $model->error_message = json_encode($error->getMessage());
                }
                $model->save();
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        $plans = Plan::find()->select(['name', 'id'])->column();

        return $this->render('create', [
            'model' => $model,
            'plans' => $plans,
            'user_id' => $userId,
        ]);
    }

    /**
     * Updates an existing Credit model.
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

        $plans = Plan::find()->select(['name', 'id'])->column();

        return $this->render('update', [
            'model' => $model,
            'user_id' => $model->user->id,
            'plans' => $plans,
        ]);
    }

    /**
     * Deletes an existing Credit model.
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
     * Finds the Credit model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Credit the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Credit::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}