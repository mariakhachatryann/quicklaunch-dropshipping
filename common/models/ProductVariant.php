<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;


/**
 * This is the model class for table "product_variants".
 *
 * @property int $id
 * @property int|null $product_id
 * @property string|null $img
 * @property string|null $option1
 * @property string|null $option2
 * @property string|null $option3
 * @property string|null $sku
 * @property string|null $default_sku
 * @property float|null $price
 * @property float|null $cost
 * @property float|null $compare_at_price
 * @property int|null $inventory_quantity
 * @property int|null $inventory_item_id
 * @property int|null $shopify_variant_id
 * @property int $updated_at
 * @property int $created_at
 *
 * @property Product $product
 * @property VariantChange[] $variantChanges
// * @property VariantPriceMarkup $variantPriceMarkup
 */
class ProductVariant extends \yii\db\ActiveRecord
{

    public $getFromProductMarkup;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_variants';
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
            [['product_id', 'inventory_quantity', 'inventory_item_id', 'shopify_variant_id', 'updated_at', 'created_at'], 'integer'],
            [['price', 'compare_at_price', 'cost'], 'number'],
            ['getFromProductMarkup', 'safe'],
            [['img', 'option1', 'option2', 'option3', 'sku', 'default_sku'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],
            [['img', 'option1', 'option2', 'option3', 'sku', 'default_sku'], 'string', 'max' => 255],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['product_id', 'option1', 'option2', 'option3'], 'unique', 'targetAttribute' => ['product_id', 'option1', 'option2', 'option3'], 'message' => 'Variant already exist.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product',
            'img' => 'Img',
            'option1' => 'Option1',
            'option2' => 'Option2',
            'sku' => 'Sku',
            'default_sku' => 'Default SKU',
            'price' => 'Price',
            'compare_at_price' => 'Compare At Price',
            'inventory_quantity' => 'Inventory Quantity',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At'
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


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVariantChanges()
    {
        return $this->hasMany(ProductVariant::class, ['variant_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVariantPriceMarkup()
    {
        return $this->hasOne(VariantPriceMarkup::class, ['variant_id' => 'id']);
    }

    /**
     * @return string
     */
    public function calculateAdditionalAttributes()
    {
        if (!empty($this->variantPriceMarkup)) {
            return $this->calculateAdditionAttributesFromVariantPriceMarkup();
        }

        return "original_price='$this->cost'";
    }

    /**
     * @param $markup
     * @param $amount
     * @param $percent
     * @return float
     */
    public function calculateCost($markup, $amount, $percent): ?float
    {
        
        try {
            $cost = round($markup === 0 ? $this->price / ($percent / 100 + 1) : $this->price - $amount, 2);
            if ($cost < 0) {
                $cost = 0;
            }
            return $cost;
        } catch (\Throwable $exception) {
            \Yii::error([$markup, $amount, $percent, $this->attributes], 'CalculateCostError');
            return $this->price;
        }
        
    }
    
    

    /**
     * @return string
     */
    protected function calculateAdditionAttributesFromVariantPriceMarkup()
    {
        return "compare_at_price_by_amount='{$this->variantPriceMarkup->compare_at_price_by_amount}'
          price_by_percent='{$this->variantPriceMarkup->price_by_percent}'
          price_markup='{$this->variantPriceMarkup->price_markup}'
          compare_at_price_markup='{$this->variantPriceMarkup->compare_at_price_markup}'
          price_by_amount='{$this->variantPriceMarkup->price_by_amount}'
          compare_at_price_by_percent='{$this->variantPriceMarkup->compare_at_price_by_percent}'
          original_price={$this->cost}";
    }

    public function getVariantCost(): float
    {
        try {
            if ($this->cost) {
                return $this->cost;
            }
            /* @var ProductPriceMarkup | VariantPriceMarkup*/
            $markupObject = $this->variantPriceMarkup ?? $this->product->productPriceMarkup;
            
            $markup = $markupObject->price_markup;
            $amount = $markupObject->price_by_amount;
            $percent =$markupObject->price_by_percent;
            
            return $this->calculateCost($markup, $amount, $percent);
        } catch (\Throwable $exception) {
            return $this->price;
        }

    }

    /**
     * @param $maxPrice
     * @param $oldRate
     * @return array
     */
    public function updatePrice(&$maxPrice, $oldRate, $newRate): array
    {
        $priceMarkupData = $this->variantPriceMarkup ?? $this->product->productPriceMarkup;
        $originalPrice = $priceMarkupData->price_markup == 1 ?
            ($this->price - $priceMarkupData->price_by_amount) / $oldRate :
            (($this->price * 100) / (100 + $priceMarkupData->price_by_percent)) / $oldRate;

        $originalPrice = $originalPrice * $newRate;

        return $this->calculatePrice($originalPrice, $priceMarkupData, $maxPrice);
    }

    /**
     * @param $maxPrice
     * @param $oldMarkup
     * @return array
     */
    public function updatePriceWithMarkup(&$maxPrice, $oldMarkup): array
    {
        $priceMarkupData = $this->variantPriceMarkup ?? $this->product->productPriceMarkup;
        $priceMarkupOldData = $this->variantPriceMarkup ?? $oldMarkup;

        $originalPrice = $priceMarkupOldData->price_markup == 1 ?
            ($this->price - $priceMarkupOldData->price_by_amount) :
            (($this->price * 100) / (100 + $priceMarkupOldData->price_by_percent));

        return $this->calculatePrice($originalPrice, $priceMarkupData, $maxPrice);
    }

    /**
     * @param $originalPrice
     * @param $priceMarkupData
     * @param $maxPrice
     * @return array
     */
    private function calculatePrice($originalPrice, $priceMarkupData, &$maxPrice):array
    {
        $price = $priceMarkupData->price_markup == 1 ?
            number_format($originalPrice + $priceMarkupData->price_by_amount, 2, '.', '') :
            number_format($originalPrice + (($originalPrice * $priceMarkupData->price_by_percent) / 100), 2, '.', '');
        $compare_at_price = $priceMarkupData->compare_at_price_markup == 1 ?
            number_format($price + $priceMarkupData->compare_at_price_by_amount, 2, '.', '') :
            number_format($price + (($price * $priceMarkupData->compare_at_price_by_percent) / 100), 2, '.', '');

        if ($price > $maxPrice) {
            $maxPrice = $price;
        }

        return ['price' => $price, 'compare_at_price' => $compare_at_price];
    }
    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
         
            if ($this->cost < 0) {
                $this->cost = 0;
            }
            return true;
        }
        return false;
    }
    
}
