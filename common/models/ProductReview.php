<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "product_reviews".
 *
 * @property int $id
 * @property int|null $product_id
 * @property string|null $reviewer_name
 * @property float|null $rate
 * @property string|null $review
 * @property int|null $date
 * @property int|null $user_id
 * @property int|null $status
 * @property int|null $shopify_product_id
 * @property string|null $review_hash
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property Product $product
 * @property User $user
 */
class ProductReview extends \yii\db\ActiveRecord
{
    const STATUS_PENDING = 0;
    const STATUS_PUBLISHED = 1;

    const STATUSES = [
        self::STATUS_PUBLISHED => 'Published',
        self::STATUS_PENDING => 'Unpublished',
    ];

    public $reviewImages;
    public $shop;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_reviews';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'date', 'user_id', 'status', 'shopify_product_id', 'created_at', 'updated_at'], 'integer'],
            [['rate'], 'number'],
            [['review'], 'string'],
            [['reviewer_name', 'review_hash'], 'string', 'max' => 255],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
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
            'product_id' => 'Product ID',
            'reviewer_name' => 'Reviewer Name',
            'rate' => 'Rate',
            'review' => 'Review',
            'date' => 'Date',
            'user_id' => 'User ID',
            'status' => 'Status',
            'shopify_product_id' => 'Shopify Product ID',
            'review_hash' => 'Review Hash',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id'])->via('product');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductReviewImages()
    {
        return $this->hasMany(ProductReviewImage::class, ['product_review_id' => 'id']);
    }

    public function getProductReviewFirstImage()
    {
        return $this->getProductReviewImages()->limit(1)->one();
    }

    public function getFormattedDate()
    {
        $user = $this->user;
        $format = $user->userSetting->dateFormat();

        return date($format, $this->date);
    }

    public function getIsPublished(): bool
    {
        return $this->status == static::STATUS_PUBLISHED;
    }

    public function getStatusName(): ?string
    {
        return static::STATUSES[$this->status] ?? null;
    }
}
