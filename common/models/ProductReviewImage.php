<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%product_reviews_images}}".
 *
 * @property int $id
 * @property int $product_review_id
 * @property string $image_url
 * @property int $created_at
 * @property int $updated_at
 *
 * @property ProductReview $productReview
 */
class ProductReviewImage extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%product_reviews_images}}';
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
            [['product_review_id', 'created_at', 'updated_at'], 'integer'],
            [['image_url'], 'string', 'max' => 255],
            [['product_review_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductReview::class, 'targetAttribute' => ['product_review_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_review_id' => 'Product Review ID',
            'image_url' => 'Image Url',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductReview()
    {
        return $this->hasOne(ProductReview::class, ['id' => 'product_review_id']);
    }
}
