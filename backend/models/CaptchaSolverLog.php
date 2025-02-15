<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "captcha_solver_logs".
 *
 * @property int $id
 * @property int $admin_id
 * @property int|null $activated_at
 * @property int|null $deactivated_at
 *
 * @property Admin $admin
 */
class CaptchaSolverLog extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'activated_at',
                'updatedAtAttribute' => 'deactivated_at',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'captcha_solver_logs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['admin_id'], 'required'],
            [['admin_id', 'activated_at', 'deactivated_at'], 'integer'],
            [['admin_id'], 'exist', 'skipOnError' => true, 'targetClass' => Admin::class, 'targetAttribute' => ['admin_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'admin_id' => 'Admin ID',
            'activated_at' => 'Activated At',
            'deactivated_at' => 'Deactivated At',
        ];
    }

    /**
     * Gets query for [[Admin]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAdmin()
    {
        return $this->hasOne(Admin::class, ['id' => 'admin_id']);
    }

    public static function getActiveTimeForToday($adminId)
    {
        $query = "
            SELECT SUM(deactivated_at - activated_at) AS total_active_time
            FROM captcha_solver_logs
            WHERE admin_id = :adminId
            AND activated_at >= :startOfDay
            AND activated_at < :endOfDay
            AND deactivated_at IS NOT NULL
        ";

        return Yii::$app->db->createCommand($query)
            ->bindValue(':adminId', $adminId)
            ->bindValue(':startOfDay', strtotime('today 00:00:00'))
            ->bindValue(':endOfDay', strtotime('tomorrow 00:00:00'))
            ->queryScalar();
    }

    public static function getActiveTimeForMonth($adminId)
    {
        $query = "
            SELECT SUM(deactivated_at - activated_at) AS total_active_time
            FROM captcha_solver_logs
            WHERE admin_id = :adminId
            AND activated_at >= :startOfMonth
            AND activated_at < :endOfMonth
            AND deactivated_at IS NOT NULL
        ";

        return Yii::$app->db->createCommand($query)
            ->bindValue(':adminId', $adminId)
            ->bindValue(':startOfMonth', strtotime('first day of this month 00:00:00'))
            ->bindValue(':endOfMonth', strtotime('first day of next month 00:00:00'))
            ->queryScalar();
    }
}
