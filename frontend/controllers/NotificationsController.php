<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 05.07.2019
 * Time: 13:10
 */

namespace frontend\controllers;


use frontend\models\api\Notification;
use common\models\User;
use common\models\UserNotification;
use \yii\web\Request;
use yii\web\Controller;
use Yii;
use yii\web\Response;

class NotificationsController extends UserController
{
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        /* @var User $user*/
        $notifications = $user->getNotifications()->all();
        return $this->render('index', compact('notifications'));
    }


    public function actionNewNotifications()
    {
        $user = Yii::$app->user->identity;
        /* @var User $user*/
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $newNotifications = $user->getNewNotifications();


        $lastNotificationId = $newNotifications ? $newNotifications[count($newNotifications)-1]->id : 0 ;
        return [
            'success' => 1,
            'notifications' => $newNotifications,
            'lastId' => $lastNotificationId
        ];
    }


    public function actionSeen(Request $request)
    {
        if($request->isAjax && $request->post()) {
            $userId = Yii::$app->user->identity->id;
            $ids = json_decode($request->post('ids'));
            foreach($ids as $id) {
                $notification = Notification::findOne($id);
                $userNotification = UserNotification::findOne(['notification_id' => $id, 'user_id' => $userId]);
                if (!$userNotification) {
                    $userNotification = new UserNotification();
                    $userNotification->user_id = $userId;
                    $userNotification->notification_id = $id;
                    $userNotification->save();
                }
            }
            return $this->response->isSuccessful;
        }
        return $this->response->isClientError;
    }
}