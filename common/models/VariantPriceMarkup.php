<?php


namespace common\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%variant_price_markups}}".
 *
 * @property int $id
 * @property int $variant_id
 * @property int $price_markup
 * @property double $price_by_percent
 * @property double $price_by_amount
 * @property int $compare_at_price_markup
 * @property double $compare_at_price_by_amount
 * @property double $compare_at_price_by_percent
 * @property int $created_at
 * @property int $updated_at

 * @property ProductVariant $variant
 */
class VariantPriceMarkup extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%variant_price_markups}}';
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
            [['price_by_percent', 'price_by_amount', 'compare_at_price_by_amount', 'compare_at_price_by_percent'], 'double'],
            [['price_markup', 'compare_at_price_markup'], 'required'],
            [['price_markup', 'compare_at_price_markup'], 'integer'],
            [['variant_id'], 'integer'],
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
            'variant_id' => 'Variant ID',
            'price_markup' => 'Price Markup',
            'compare_at_price_markup' => 'Compare Price Markup',
            'price_by_percent' => 'Price By Percent',
            'price_by_amount' => 'Price By Amount',
            'compare_at_price_by_amount' => 'Compare Price By Amount',
            'compare_at_price_by_percent' => 'Compare Price By Amount',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVariant()
    {
        return $this->hasOne(ProductVariant::class, ['id' => 'variant_id']);
    }
}