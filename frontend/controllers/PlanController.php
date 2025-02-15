<?php

namespace frontend\controllers;

use common\models\Plan;
use common\models\PromoCode;
use common\models\User;
use Yii;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;


/**
 * PlanController implements the CRUD actions for Plan model.
 */
class PlanController extends UserController
{
    /**
     * Lists all Plan models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->redirect(Url::toRoute(['profile/subscribe']));
        return $this->render('index', compact('plans'));
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

    public function actionCheckPromo()
    {
        $planId = Yii::$app->request->post('planId');
        $promo = Yii::$app->request->post('promo');
        if ($planId && $promo) {
            $user = Yii::$app->user->identity;
            $promo = PromoCode::getPromoByPlan($planId, $promo, $user->id);
            if (!$promo) {
                return json_encode(['success' => 0, 'message' => 'Promo not found']);
            }else {
                if ($promo->active_until < time()) {
                    return json_encode(['success' => 0, 'message' => 'Promo has been expired']);
                }else {
                    return json_encode(['success' => 1, 'message' => 'Promo has been added']);
                }
            }
        }
    }

}
