<?php
namespace console\controllers;

use backend\models\Admin;
use backend\models\ImportQueueDailyLogs;
use common\models\AlertCaptcha;
use common\models\CancelledPlan;
use common\models\Email;
use common\models\Feature;
use common\models\ImportQueue;
use common\models\Plan;
use common\models\PlanChargeRequest;
use common\models\PlanStatistic;
use common\models\User;
use common\models\UserCharge;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\console\Controller;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class CronController extends Controller
{
    public function actionCheckCancelledPlans()
    {

        $cancelledPlans = CancelledPlan::find()
            ->andWhere(['<', 'cancellation_date', time()])
            ->andWhere(['status' => CancelledPlan::STATUS_PENDING])
            ->all();

        foreach ($cancelledPlans as $cancelledPlan) {
            /* @var $cancelledPlan CancelledPlan*/
            $user = User::findOne($cancelledPlan->user_id);
            $user->plan_id = null;
            $user->plan_status = User::PLAN_STATUS_INACTIVE;
            $user->cancelled_plan = 0;
            $user->save();
            $cancelledPlan->status = CancelledPlan::STATUS_CANCELLED;
            $cancelledPlan->save();
        }
    }

    public function actionSendInvitationEmail()
    {
        $plans = Plan::find()->all();
        $allFeatures = Feature::getAllFeatures();

        $content = Yii::$app->view->render('@frontend/views/email/index', compact('plans','allFeatures'));

        $from = ['sheinimportercommail@gmail.com' => Yii::$app->params['appName']];

        return true;

        $emailsToSend = Email::find()->andWhere(['status' => 0])->limit(50)->asArray()->all();

        foreach (array_chunk($emailsToSend, 10) as $emailsModels) {

            echo \Yii::$app->mailer->compose()
                ->setTo(ArrayHelper::getColumn($emailsModels, 'email'))
                ->setFrom($from)
                ->setSubject('RE: Free import products from shein, aliexpress, alibaba, etsy, ebay to your shopify store')
                ->setHtmlBody($content)
                ->send();

            Email::updateAll(
                ['status' => 1],
                ['IN', 'id', ArrayHelper::getColumn($emailsModels, 'id')]
            );

        }

    }

    public function actionCheckStores()
    {
        $query = User::find()->where(['status' => User::STATUS_ACTIVE])
            ->where(['plan_status' => Plan::PLAN_ACTIVE])->orderBy('id');

        foreach ($query->batch(100) as $users) {
            foreach ($users as $user) {
                sleep(1);
                /* @var $user User */
                $failCount = $user->fail_count;
                try {
                    $user->fail_count = 0;
                    $user->save();
                    $user->deleteWebhooks();
                    $user->setOrUpdateUserCharges();
                } catch (\Exception $e) {
                    echo $user->username,' ',$e->getMessage(),"\n";
                    echo $e->getMessage(),"\n";
                    if (!strpos($e->getMessage(), 'or access token') && !strpos($e->getMessage(), 'ble Shop')  &&
                        !strpos($e->getMessage(), 'ot Found')) {
                        continue;
                    }
                    User::updateAll(['fail_count' => ++$failCount], ['id' => $user->id]);
                    if ($failCount >= User::FAIL_COUNT_LIMIT) {
                        $user->inactiveUserPlan();
                    }
                }
            }
        }
    }

    public function actionPromote()
    {
        $users = User::find()->where(['plan_status' => User::PLAN_STATUS_ACTIVE])
            ->andWhere(['<', 'created_at', strtotime('-1 month')])
            ->all();

        foreach ($users as $user) {
            /* @var User $user*/
            $user->sendEmail(
                $user->email,
                '1 month Free access to SheinImporter Premium plan for ' . User::getDomain($user->username),
                '
                <p>Hey, if you are happy with our app then you can get 1 month free access to our Premium subscription plan with all available features</p>
                <p>You just need to <a href="https://apps.shopify.com/shein-importer">add 5 star feedback to our app</a> , then <a href="https://app.shionimporter.site/site/contact">open ticket here</a></p>
                <p>We will give you 1 month premium plan after that</p>
    
            ');
        }

    }

    public function actionImportEmails()
    {
        $file = fopen(Yii::getAlias('@console/emails.csv'), 'r');
        while ($email = fgetcsv($file)) {
            $emailModel = new Email();
            $emailModel->email = $email[0];

            $emailModel->save();
            echo $emailModel->id,"\n";
        }
    }

	public function actionUpdatePlanStatistics()
	{
		$todaysRecords = User::find()
			->select([
				new Expression('COUNT(*) as total'),
				'plan_id',
			])
			->andWhere(['plan_status' => Plan::PLAN_ACTIVE])
			->groupBy('plan_id')
			->orderBy('total DESC')
			->indexBy('plan_id')
			->asArray()->all();

		foreach ($todaysRecords as $record) {
			$planStatistic = new PlanStatistic();
			$planStatistic->plan_id = $record['plan_id'];
			$planStatistic->total = $record['total'];
			$planStatistic->date = strtotime(date('Y-m-d'));
			$planStatistic->save();
		}
	}

	public function actionDeleteManuallyUpgradedPlanUsers()
	{
	    $basicPlan = Plan::getBasicPlan();
		/** @var User[] $users */
        if (!$basicPlan) {
            Yii::error('no basic plan', 'NoBasicPlan');
        }
		$users = User::find()
			->joinWith('userCharges c')
			->where([
			    'is_manual_plan' => User::MANUALLY_UPGRADED,
                'plan_id' => $basicPlan->id,
            ])->andWhere(['<', 'left_review_at', strtotime('-1 month')])
			->all();

		$freePlan = Plan::getFreePlan();
		$freePlanId = $freePlan->id;

		foreach ($users as $user) {
			if (!$user->userCharges) {
				$user->is_manual_plan = User::NOT_MANUALLY_UPGRADED;
				$user->plan_id = $freePlanId;

				$user->save(false);
                Yii::error([$user->id, $user->username, $user->plan_id, $user->plan_status], 'FreeBasicPlanExpired');
			}
		}
	}

    public function actionUpdateInactiveAdminStatus()
    {
        $inactiveTimeLimit = strtotime('-30 minutes');
        $activeAdmins = Admin::find()->where(['is_online' => 1])->all();

        foreach ($activeAdmins as $admin) {
            $lastCaptchaTime = AlertCaptcha::find()
                ->where(['admin_id' => $admin->id])
                ->orderBy(['taken_at' => SORT_DESC])
                ->select('taken_at')
                ->scalar();

            if (!$lastCaptchaTime || strtotime($lastCaptchaTime) < $inactiveTimeLimit) {
                $admin->is_online = 0;
                $admin->save(false);
            }
        }
    }

    public function actionRecordImportQueueLogs()
    {
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $totalCount = ImportQueue::find()
            ->where(['>=', 'FROM_UNIXTIME(created_at)', $yesterday . ' 00:00:00'])
            ->andWhere(['<=', 'FROM_UNIXTIME(created_at)', $yesterday . ' 23:59:59'])
            ->count();

        $successfulCount = ImportQueue::find()
            ->where(['status' => ImportQueue::STATUS_SUCCESSFUL])
            ->andWhere(['>=', 'FROM_UNIXTIME(created_at)', $yesterday . ' 00:00:00'])
            ->andWhere(['<=', 'FROM_UNIXTIME(created_at)', $yesterday . ' 23:59:59'])
            ->count();

        $errorCount = ImportQueue::find()
            ->where(['status' => ImportQueue::STATUS_ERROR])
            ->andWhere(['>=', 'FROM_UNIXTIME(created_at)', $yesterday . ' 00:00:00'])
            ->andWhere(['<=', 'FROM_UNIXTIME(created_at)', $yesterday . ' 23:59:59'])
            ->count();

        $log = new ImportQueueDailyLogs();
        $log->total_count = $totalCount;
        $log->successful = $successfulCount;
        $log->error = $errorCount;
        $log->date = $yesterday;
        $log->save();
    }
}