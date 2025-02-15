<?php

namespace common\models;

use backend\models\Admin;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%alert_captchas}}".
 *
 * @property int $id
 * @property int|null $import_queue_id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $taken_at
 * @property int|null $handler
 * @property int|null $status
 *
 * @property int $duration
 * @property ImportQueue $importQueue
 */
class AlertCaptcha extends \yii\db\ActiveRecord
{

    const STATUS_PENDING = 0;
    const STATUS_SOLVED = 1;
    const STATUS_EXPIRED = 2;
    const STATUS_CANCELLED = 3;
    const STATUS_TAKEN = 4;

    const STATUS_PENDING_TEXT = 'Pending';
    const STATUS_SOLVED_TEXT = 'Solved';
    const STATUS_EXPIRED_TEXT = 'Expired';
    const STATUS_CANCELLED_TEXT = 'Cancelled';
    const STATUS_TAKEN_TEXT = 'Taken';

    const STATUSES = [
        self::STATUS_PENDING => self::STATUS_PENDING_TEXT,
        self::STATUS_SOLVED => self::STATUS_SOLVED_TEXT,
        self::STATUS_EXPIRED => self::STATUS_EXPIRED_TEXT,
        self::STATUS_CANCELLED => self::STATUS_CANCELLED_TEXT,
        self::STATUS_TAKEN => self::STATUS_TAKEN_TEXT,
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%alert_captchas}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['import_queue_id', 'created_at', 'updated_at', 'handler', 'status', 'admin_id', 'taken_at'], 'integer'],
            [['import_queue_id'], 'exist', 'skipOnError' => true, 'targetClass' => ImportQueue::class, 'targetAttribute' => ['import_queue_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'import_queue_id' => 'Import Queue ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'admin_id' => 'Admin ID',
            'taken_at' => 'Taken At',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class
        ];
    }

    public function getAdmin()
    {
        return $this->hasOne(Admin::class, ['id' => 'admin_id']);
    }

    /**
     * Gets query for [[ImportQueue]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImportQueue()
    {
        return $this->hasOne(ImportQueue::class, ['id' => 'import_queue_id']);
    }

    public function getStatusLabel()
    {
        return self::STATUSES[$this->status];
    }

    public function getSolvingDuration()
    {
        if ($this->taken_at) {
            return time() - $this->taken_at;
        }
        return null;
    }

    private static function buildDurationQuery($start, $end, $adminId = null)
    {
        $query = static::find()
            ->where(['status' => [AlertCaptcha::STATUS_SOLVED]])
            ->andWhere(['>=', 'updated_at', $start])
            ->andWhere(['<=', 'updated_at', $end]);

        if ($adminId !== null) {
            $query->andWhere(['admin_id' => $adminId]);
        }

        return $query;
    }

    public static function getSolvingDurationForDay($adminId)
    {
        $startOfDay = strtotime('today 00:00:00');
        $endOfDay = strtotime('today 23:59:59');

        return static::buildDurationQuery($startOfDay, $endOfDay, $adminId)
            ->average('duration');
    }

    public static function getSolvingDurationForMonth($adminId)
    {

        $startOfMonth = strtotime('first day of this month 00:00:00');
        $endOfMonth = strtotime('last day of this month 23:59:59');

        return static::buildDurationQuery($startOfMonth, $endOfMonth, $adminId)
            ->average('duration');
    }

    public function solve()
    {
        if ($this->status == self::STATUS_TAKEN) {
            $this->duration = $this->getSolvingDuration();
            $this->status = static::STATUS_SOLVED;
            return $this->save();
        }
        return false;
    }

    public static function getDailyChartData($adminId)
    {
        $data = [];
        $labels = [];

        $startOfMonth = strtotime('first day of this month 00:00:00');
        $endOfMonth = strtotime('last day of this month 23:59:59');

        for ($day = $startOfMonth; $day <= $endOfMonth; $day += 86400) {
            $startOfDay = $day;
            $endOfDay = $day + 86400 - 1;

            $averageDuration = static::buildDurationQuery($startOfDay, $endOfDay, $adminId)
                ->average('duration');

            $labels[] = date('Y-m-d', $startOfDay);
            $data[] = round($averageDuration, 2) ?: 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }


    public static function getMonthlyChartData($adminId)
    {
        $data = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $startOfMonth = strtotime("first day of -$i month 00:00:00");
            $endOfMonth = strtotime("last day of -$i month 23:59:59");

            $averageDuration = static::buildDurationQuery($startOfMonth, $endOfMonth, $adminId)
                ->average('duration');

            $labels[] = date('F Y', $startOfMonth);
            $data[] = round($averageDuration, 2);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    public static function calculateAverageDurationPerAdmin(CaptchaSearch $searchModel): array
    {
        $query = AlertCaptcha::find()
            ->where(['status' => AlertCaptcha::STATUS_SOLVED])
            ->select(['admin_id', 'average_duration' => 'AVG(duration)']);

        if (!empty($searchModel->date_range)) {
            list($dateFrom, $dateTo) = explode(' to ', $searchModel->date_range);

            $dateFrom = strtotime($dateFrom . ' 00:00:00');
            $dateTo = strtotime($dateTo . ' 23:59:59');

            $query->andWhere(['>=', 'updated_at', $dateFrom])
                ->andWhere(['<=', 'updated_at', $dateTo]);
        }

        $query->groupBy('admin_id');

        $result = $query->asArray()->all();

        $solverDuration = [];
        foreach ($result as $row) {
            $solverDuration[$row['admin_id']] = round($row['average_duration'], 2);
        }

        return $solverDuration;
    }








}
