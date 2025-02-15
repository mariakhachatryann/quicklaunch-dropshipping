<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;

/**
 * BulkImport model
 *
 * @property integer $id
 * @property integer $status
 * @property integer $bulk_monitoring_id
 * @property string $url
 * @property integer $created_at
 * @property integer $product_id
 * @property integer $updated_at
 * @property BulkMonitoring $bulkMonitoring
 * @property Product $product
 */
class BulkMonitoringItem extends \yii\db\ActiveRecord
{

    const STATUS_PENDING = 0;
    const STATUS_APPLIED = 1;
    const STATUS_ERROR = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bulk_monitoring_items}}';
    }

    /**
     * @return string[]
     */
    public function behaviors(): array
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
            [['url'], 'string', 'max' => 600],
            [['status'], 'integer'],
            [['url'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],
            [['bulk_monitoring_id'], 'exist', 'skipOnError' => true, 'targetClass' => BulkMonitoring::class, 'targetAttribute' => ['bulk_monitoring_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['bulk_monitoring_id', 'product_id'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Url',
            'status' => 'Status',
            'bulk_monitoring_id' => 'Bulk Monitoring',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBulkMonitoring(): \yii\db\ActiveQuery
    {
        return $this->hasMany(BulkMonitoring::class, ['id' => 'bulk_monitoring_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Product::class, ['id' => 'bulk_monitoring_id']);
    }

}