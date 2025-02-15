<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;

/**
 * BulkImport model
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property User[] $user
 * @property BulkMonitoringItem[] $bulkMonitoringItems
 */
class BulkMonitoring extends \yii\db\ActiveRecord
{
    const MAX_IMPORT_TIME = 60;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bulk_monitoring}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['user_id'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User Id',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBulkMonitoringItems(): \yii\db\ActiveQuery
    {
        return $this->hasMany(BulkMonitoringItem::class, ['bulk_monitoring_id' => 'id']);
    }

}