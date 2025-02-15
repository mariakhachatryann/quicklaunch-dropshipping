<?php

namespace backend\controllers;

use Yii;
use common\models\Feature;
use backend\models\FeatureSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * FeatureController implements the CRUD actions for Feature model.
 */
class FeatureController extends AdminController
{

    /**
     * Lists all Feature models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FeatureSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Feature model.
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
     * Finds the Feature model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Feature the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Feature::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Creates a new Feature model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Feature();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Feature model.
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
     * Deletes an existing Feature model.
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

    public function actionSort()
    {
        $model = new Feature();
        $features = Feature::find()->orderBy('sort')->indexBy('id')->all();

        if ($model->load(Yii::$app->request->post())) {
            $sortNumbers = explode(',', $model->sort);

            foreach ($sortNumbers as $sort => $id) {
                $feature = $features[$id];
                $feature->sort = $sort;
                $feature->save();
            }
            $model->save();

            return $this->redirect('index');
        }

        $items = [];
        foreach ($features as $feature) {
            $items[$feature->id] = ['content' => $feature->name];
        }

        return $this->render('sort', compact('model', 'items'));
    }


}
