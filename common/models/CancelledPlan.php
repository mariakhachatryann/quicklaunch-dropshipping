<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%cancelled_plans}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $cancellation_date
 * @property boolean $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 */
class CancelledPlan extends \yii\db\ActiveRecord
{
    const STATUS_CANCELLED = 0;
    const STATUS_PENDING = 1;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%cancelled_plans}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'cancellation_date', 'created_at', 'updated_at', 'status'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'cancellation_date' => 'Cancellation Date',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
