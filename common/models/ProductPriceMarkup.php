<?php


namespace common\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%product_price_markups}}".
 *
 * @property int $id
 * @property int $product_id
 * @property int $price_markup
 * @property double $price_by_percent
 * @property double $price_by_amount
 * @property int $compare_at_price_markup
 * @property double $compare_at_price_by_amount
 * @property double $compare_at_price_by_percent
 * @property int $created_at
 * @property int $updated_at
 */
class ProductPriceMarkup extends \yii\db\ActiveRecord
{

    const SCENARIO_UPDATE_PRODUCT_MARKUP = 'updateProductMarkup';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%product_price_markups}}';
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
            [['price_markup', 'compare_at_price_markup'], 'integer'],
            [['product_id'], 'integer'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_UPDATE_PRODUCT_MARKUP] = [
            'price_by_percent',
            'price_by_amount',
            'price_markup',
            'compare_at_price_by_amount',
            'compare_at_price_by_percent',
            'compare_at_price_markup',
        ];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
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
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }
}