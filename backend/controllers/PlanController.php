<?php

namespace backend\controllers;

use common\models\AvailableSite;
use common\models\Feature;
use common\models\PlanFeature;
use common\models\PlanSite;
use common\models\PlanStatistic;
use common\models\User;
use common\models\UserSetting;
use Yii;
use common\models\Plan;
use backend\models\PlanSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PlanController implements the CRUD actions for Plan model.
 */
class PlanController extends AdminController
{


    /**
     * Lists all Plan models.
     * @return mixed
     */
    public function actionIndex($planStatisticDate = false)
    {
        $searchModel = new PlanSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

		if ($planStatisticDate) {
			$startDate = strtotime(explode(' - ', $planStatisticDate)[0]);
			$endDate = strtotime(explode(' - ', $planStatisticDate)[1]);
		} else {
			$startDate = time() - 30 * 24 * 3600;
			$endDate = time();
		}
		
		$statistics = PlanStatistic::getStatistics($startDate, $endDate);
		$range = [];
		for ($date = $startDate; $date <= $endDate; $date += 24 * 3600) {
			$range[] = date('d M', $date);
		}

		$statisticsForTable = PlanStatistic::getStatisticsForTable($startDate, $endDate);
		
		return $this->render('index', compact(
			'searchModel', 'dataProvider', 'planStatisticDate', 'statistics', 'range', 'statisticsForTable'
		));
    }

    /**
     * Displays a single Plan model.
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
     * Finds the Plan model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Plan the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Plan::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Creates a new Plan model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Plan();
        $features = Feature::find()->select(['name', 'id'])->indexBy('id')->column();
        $sites = AvailableSite::find()->select(['name', 'id'])->indexBy('id')->column();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->featureIds){
                foreach ($model->featureIds as $featureId) {
                    $planFeature = new PlanFeature();
                    $planFeature->plan_id = $model->id;
                    $planFeature->feature_id = $featureId;
                    $planFeature->save();
                }
            }
            foreach ($model->siteIds as $siteId) {
                $planSite = new PlanSite();
                $planSite->plan_id = $model->id;
                $planSite->site_id = $siteId;
                $planSite->save();
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', compact('model', 'features', 'sites'));
    }

    /**
     * Updates an existing Plan model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $features = Feature::find()->select(['name', 'id'])->indexBy('id')->column();
        $sites = AvailableSite::find()->select(['name', 'id'])->indexBy('id')->column();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $planOldFeatureIds = $model->getFeatures()->select('id')->column();
            $planNewFeatureIds = $model->featureIds;
            $removedFeatureIds = $planNewFeatureIds ? array_diff($planOldFeatureIds, $planNewFeatureIds) : $planOldFeatureIds;

            if (!empty($removedFeatureIds)) {
                $users = User::find()->where(['plan_id' => $model->id])->select('id');
                $settingKeys = Feature::find()->where(['id' => $removedFeatureIds])->select('setting_key')->column();
                $updateSettings = [];
                foreach ($settingKeys as $settingKey) {
                    $updateSettings[$settingKey] = 0;
                }
                try {
                    UserSetting::updateAll($updateSettings, ['user_id' => $users]);
                } catch (\Throwable $e){
                }
            }

            PlanFeature::deleteAll(['plan_id' => $model->id]);
            PlanSite::deleteAll(['plan_id' => $model->id]);

            if (!empty($model->featureIds)) {
                foreach ($model->featureIds as $featureId) {
                    $planFeature = new PlanFeature();
                    $planFeature->plan_id = $model->id;
                    $planFeature->feature_id = $featureId;
                    $planFeature->save();
                }
            }
            if (!empty($model->siteIds)) {
                foreach ($model->siteIds as $siteId) {
                    $planSite = new PlanSite();
                    $planSite->plan_id = $model->id;
                    $planSite->site_id = $siteId;
                    $planSite->save();
                }
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        $model->featureIds = $model->features;
        $model->siteIds = $model->sites;
        return $this->render('update', compact('model', 'features', 'sites'));
    }

    /**
     * Deletes an existing Plan model.
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
}
