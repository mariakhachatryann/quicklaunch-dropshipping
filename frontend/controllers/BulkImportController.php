<?php


namespace frontend\controllers;


use common\models\BulkImport;
use common\models\Feature;
use common\models\MultipleImport;
use common\models\User;
use common\models\Video;
use frontend\models\api\Product;
use Yii;
use yii\web\NotFoundHttpException;

class BulkImportController extends UserController
{
    public function actionCreate()
    {
        $model = new BulkImport();
        $user = Yii::$app->user->identity;
        /* @var  $user User*/
        $planFeatures = Feature::getPlanSettingkeys($user);
        $allowBulkImport = isset($planFeatures['bulk_import']) && $planFeatures['bulk_import'];
       if (\Yii::$app->request->isPost) {
           if (!$allowBulkImport) {
               return null;
           }
            $user = \Yii::$app->user->identity;
            $model->category_url = \Yii::$app->request->post('url');
            $model->user_id = $user->id;
            if ($model->validate() && $model->save()) {
                return json_encode(['success' => 1, 'id' => $model->id]);
            }

            return json_encode(['success' => 0]);
       }

        $trainingVideos = Video::find()->indexBy('id')->where(['id' => Video::IMPORT_MULTIPLE_VIDEO_IDS])->all();
        return $this->render('create', compact('model', 'allowBulkImport', 'trainingVideos'));
    }

}