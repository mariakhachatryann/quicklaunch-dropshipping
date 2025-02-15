<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "variant_changes".
 *
 * @property int $id
 * @property int|null $variant_id
 * @property float|null $old_price
 * @property float|null $new_price
 * @property float|null $new_compare_at_price
 * @property float|null $old_compare_at_price
 * @property int|null $old_inventory_quantity
 * @property int|null $new_inventory_quantity
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property ProductVariant $variant
 */
class VariantChange extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%variant_changes}}';
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
            [['variant_id', 'old_inventory_quantity', 'new_inventory_quantity', 'created_at', 'updated_at'], 'integer'],
            [['old_price', 'new_price', 'new_compare_at_price', 'old_compare_at_price'], 'number'],
            [['variant_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductVariant::class, 'targetAttribute' => ['variant_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'variant_id' => 'Variant',
            'old_price' => 'Old Price',
            'new_price' => 'New Price',
            'new_compare_at_price' => 'New Compare At Price',
            'old_compare_at_price' => 'Old Compare At Price',
            'old_inventory_quantity' => 'Old Quantity',
            'new_inventory_quantity' => 'New Quantity',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Variant]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVariant()
    {
        return $this->hasOne(ProductVariant::class, ['id' => 'variant_id']);
    }

}
