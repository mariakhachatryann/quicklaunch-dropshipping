<?php

namespace backend\controllers;

use backend\models\Admin;
use common\models\Lead;
use common\models\User;
use Exception;
use frontend\models\api\ProductGenerateContent;
use Yii;
use common\models\LeadMessage;
use backend\models\LeadMessageImage;
use backend\models\LeadMessageSearch;
use backend\controllers\AdminController;
use yii\base\BaseObject;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\UploadForm;
use yii\web\UploadedFile;

/**
 * LeadMessageController implements the CRUD actions for LeadMessage model.
 */
class LeadMessageController extends AdminController
{
    protected $allowedRoles = [Admin::ROLE_SUPPORT];

    public function actions()
    {

        return [
            'browse-images' => [
                'class' => 'bajadev\ckeditor\actions\BrowseAction',
                'quality' => 80,
                'maxWidth' => 800,
                'maxHeight' => 800,
                'useHash' => true,
                'url' => '/images/tickets/',
                'path' => '@frontend/web/images/tickets/',
            ],

            'upload-images' => [
                'class' => 'bajadev\ckeditor\actions\UploadAction',
                'quality' => 80,
                'maxWidth' => 800,
                'maxHeight' => 800,
                'useHash' => true,
                'url' => '/images/tickets/',
                'path' => '@frontend/web/images/tickets/',
            ],
        ];
    }

    /**
     * Lists all LeadMessage models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new LeadMessageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single LeadMessage model.
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
     * Creates a new LeadMessage model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($lead_id = null, $message_id = null)
    {
    
        $lead = null;
        $leadMessage = null;
        if ($lead_id) {
            $lead = Lead::findOne($lead_id);
            /* @var  $lead Lead*/
        }
        if ($message_id) {
            $leadMessage = LeadMessage::findOne($message_id);
        }
        $model = new LeadMessage();
        $file = new UploadForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $model->lead_id = $lead_id;
            $model->user_id = $lead->user_id;
            $model->sender = LeadMessage::SENDER_ADMIN;
            $file->imageFile = UploadedFile::getInstance($file, 'imageFile');
            $url = 'https://app.shionimporter.site/site/chat/' . $lead_id;

            $file->images = UploadedFile::getInstances($file, 'images');
            $imageNames = [];

            if ($file->images) {
                foreach ($file->images as $key => $image) {
                    $file->imageFile = $image;
                    if ($file->upload('lead_message_images')) {
                        if ($key == 0) {
                            $model->image = $image->name;
                        } else {
                            $imageNames[] = $image->name;
                        }
                    }
                }

            }

            if ($model->save()) {
                foreach ($imageNames as $name) {
                    $leadImage = new LeadMessageImage();
                    $leadImage->lead_message_id = $model->id;
                    $leadImage->name = $name;

                    if ($leadImage->validate()) {
                        $leadImage->save();
                    } else {
                        print_r($leadImage->getErrors());die;
                    }

                    $model->user->sendEmail(
                        $model->user->email,
                        'Answer to your ticket #' . $model->lead_id,
                        'You got answer to your ticket. <br> You can see it here - ' .
                        Html::a($url, $url)
                    );
                }
            }
    
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', compact('model', 'lead', 'file', 'leadMessage'));
    }

    /**
     * Updates an existing LeadMessage model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $lead = $model->lead;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', compact('model', 'lead'));
    }

    /**
     * Deletes an existing LeadMessage model.
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
     * @return false|string
     * @throws NotFoundHttpException
     */
    public function actionPrepareMessage()
    {
        $model = new ProductGenerateContent();

        if ($model->load(Yii::$app->request->post(), '') && $model->validate()) {
            if ($model->messageId) {
                $model->message = $this->findModel($model->messageId)->message;
            }

            try {
                $response = [
                    'success' => true,
                    'content' => trim($model->generateContent(), '"')
                ];
                return json_encode($response);
            } catch (Exception $exception) {
                Yii::error([$exception->getMessage(), $model->attributes], 'GenerateAiLeadMessageError');
            }

        }

        return json_encode([
            'success' => false
        ]);
    }
    
    /**
     * Finds the LeadMessage model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return LeadMessage the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = LeadMessage::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


}
