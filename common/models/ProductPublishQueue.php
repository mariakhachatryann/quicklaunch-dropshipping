<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%product_publish_queues}}".
 *
 * @property int $id
 * @property int|null $product_id
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property string|null $response_text
 *
 * @property Product $product
 */
class ProductPublishQueue extends \yii\db\ActiveRecord
{
    const STATUS_PENDING = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_ERROR = 2;


    const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_PUBLISHED => 'Published',
        self::STATUS_ERROR => 'Error',

    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%product_publish_queues}}';
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
            [['product_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['response_text'], 'string'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'response_text' => 'Response Text',
        ];
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }
}
