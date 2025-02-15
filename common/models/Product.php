<?php

namespace common\models;

use console\jobs\PublishJob;
use frontend\models\api\ProductData;
use frontend\models\ProductPricingRule;
use frontend\models\RecommendedProduct;
use Yii;
use yii\base\InvalidArgumentException;
use yii\behaviors\TimestampBehavior;
use yii\data\ArrayDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * This is the model class for table "products".
 *
 * @property int $id
 * @property string|null $title
 * @property int|null $user_id
 * @property string|null $src_product_url
 * @property string|null $sku
 * @property string|null $product_data
 * @property string|null $product_type
 * @property int|null $product_type_id
 * @property int|null $shopify_id
 * @property string|null $handle
 * @property int|null $is_deleted
 * @property int|null $count_variants
 * @property int|null $site_id
 * @property int|null $monitored_at
 * @property int|null $monitoring_price
 * @property int|null $monitoring_stock
 * @property int|null $currency_id
 * @property int|null $default_currency_id
 * @property float|null $currency_rate
 * @property int|null $update_currency_rate
 * @property int|null $imported_from
 * @property int|null $monitoring_reviews
 * @property int|null $monitoring_reviews_min_rate
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 *  @property float $rate
 * * @property int $reviewsCount
 * * @property string $displayPrice
 * * @property string $productImage
 * * @property User $user
 * * @property ProductReview[] $productReviews
 * * @property ProductVariant[] $productVariants
 * * @property VariantChange[] $productVariantChanges
 * * @property ProductPublishQueue[] $publishQueues
 * * @property ProductPublishQueue $activePublishQueue
 * * @property AvailableSite $site
 * * @property RecommendedProduct $recommendedProduct
 * * @property ProductType $productType
 * * @property ProductPricingRule[] $productPricingRules
 * * @property ProductBulkImportItem[] $productBulkImportItems
 * * @property ProductPriceMarkup $productPriceMarkup
 * * @property Currency $currency
 * * @property Currency $defaultCurrency
 * * @property ProductData $productDataModel
 * * @property BulkMonitoringItem[] $bulkMonitoringItems
 */
class Product extends \yii\db\ActiveRecord
{
    const DISPLAY_GRID_STYLE = 0;
    const DISPLAY_lIST_STYLE = 1;
    const DISPLAY_TABLE_STYLE = 2;

    const PRODUCT_IS_DELETED = 1;
    const PRODUCT_IS_NOT_DELETED = 0;

    const MONITORING_ENABLE = 1;
    const MONITORING_DISABLE = 0;

    const PRODUCT_IS_PUBLISHED = 1;
    const PRODUCT_IS_NOT_PUBLISHED = 0;

    const DATE_DISPLAY_FORMAT = 'l, d M Y';

    const PRODUCT_COUNT_TO_SUGGEST_REVIEW = 5;

    const SCENARIO_LOAD_CURRENCY_RATE = 'loadCurrencyRate';

    const IMPORTED_FROM_EXTENSION = 1;
    const IMPORTED_FROM_DASHBOARD = 2;

    const IMPORTED_FROM_TYPES = [
        self::IMPORTED_FROM_EXTENSION => 'Extension',
        self::IMPORTED_FROM_DASHBOARD => 'Dashboard',
    ];


    public $total;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'product_type_id', 'shopify_id', 'is_deleted', 'count_variants', 'site_id', 'monitored_at', 'monitoring_price', 'monitoring_stock', 'currency_id', 'default_currency_id', 'update_currency_rate', 'imported_from', 'monitoring_reviews', 'monitoring_reviews_min_rate', 'created_at', 'updated_at'], 'integer'],
            [['product_data'], 'string'],
            [['currency_rate'], 'number'],
            [['title', 'src_product_url', 'sku', 'product_type', 'handle'], 'string', 'max' => 255],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::class, 'targetAttribute' => ['currency_id' => 'id']],
            [['default_currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::class, 'targetAttribute' => ['default_currency_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['site_id'], 'exist', 'skipOnError' => true, 'targetClass' => AvailableSite::class, 'targetAttribute' => ['site_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'user_id' => 'User ID',
            'src_product_url' => 'Src Product Url',
            'sku' => 'Sku',
            'product_data' => 'Product Data',
            'product_type' => 'Product Type',
            'product_type_id' => 'Product Type ID',
            'shopify_id' => 'Shopify ID',
            'handle' => 'Handle',
            'is_deleted' => 'Is Deleted',
            'count_variants' => 'Count Variants',
            'site_id' => 'Site ID',
            'monitored_at' => 'Monitored At',
            'monitoring_price' => 'Monitoring Price',
            'monitoring_stock' => 'Monitoring Stock',
            'currency_id' => 'Currency ID',
            'default_currency_id' => 'Default Currency ID',
            'currency_rate' => 'Currency Rate',
            'update_currency_rate' => 'Update Currency Rate',
            'imported_from' => 'Imported From',
            'monitoring_reviews' => 'Monitoring Reviews',
            'monitoring_reviews_min_rate' => 'Monitoring Reviews Min Rate',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
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
     * Gets query for [[Currency]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    /**
     * Gets query for [[DefaultCurrency]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'default_currency_id']);
    }

    /**
     * Gets query for [[Site]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(AvailableSite::class, ['id' => 'site_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @param int|null $user_id
     * @return ArrayDataProvider
     */
    public static function getProductsBySites($user_id = null): ArrayDataProvider
    {
        $allModels = Yii::$app->cache->getOrSet('productsBySites', function () use ($user_id) {
            $queryForProducts = Product::find()
                ->alias('p')->select([
                    new Expression('COUNT(*) as total'),
                    'site_id',
                    AvailableSite::tableName() . '.name'
                ])
                ->andFilterWhere(['user_id' => $user_id])
                ->joinWith('site', false)
                ->groupBy('site_id')
                ->orderBy('total DESC')
                ->indexBy('site_id')
                ->asArray();

            $queryForProductsToday = (clone $queryForProducts)
                ->andWhere(['>', 'p.created_at', strtotime(date('Y-m-d'))]);

            $allData = $queryForProducts->all();
            $todaysData = $queryForProductsToday->all();

            foreach ($allData as $siteId => &$data) {
                $data['today'] = $todaysData[$siteId]['total'] ?? 0;
            }
            return $allData;
        }, 3600);


        return new ArrayDataProvider([
            'allModels' => $allModels,
            'pagination' => false
        ]);
    }

    public function getHandleUrl()
    {
        $handleUrl = '';
        if ($this->is_published) {
            $user = $this->getUserModel();
            $handleUrl = "{$user->shopUrl}/products/$this->handle";
        }
        return $handleUrl;
    }

    public function getUserModel(): ?User
    {
        return $this->user;
    }

    /**
     * Set variants data for sending to shopify
     * @return array
     */
    public function getFilteredVariantsData(): array
    {
        $result = [];
        $variants = $this->productVariants;
        foreach ($variants as $variant) {
            $data = $variant->attributes;
            unset($data['id']);
            unset($data['product_id']);
            unset($data['inventory_item_id']);
            $data['inventory_policy'] = 'deny';
            $data['inventory_management'] = 'shopify';
            $data['cost'] = $variant->getVariantCost();
            $result[] = $data;
        }
        return $result;
    }

    /**
     * @return yii\db\ActiveQuery
     */
    public function getProductVariants()
    {
        return $this->hasMany(ProductVariant::class, ['product_id' => 'id']);
    }

    public function getProductVariantChanges()
    {
        return $this->hasMany(VariantChange::class, ['variant_id' => 'id'])->via('productVariants');
    }

    /**
     * @return array
     */
    public function getProductData(): array
    {
        return $this->product_data ? json_decode($this->product_data, true) : [];
    }

    /**
     * @return string
     */
    public function getProductImage(): string
    {
        $productData = json_decode($this->product_data, true);
        return $productData['images'][0] ?? '';
    }

    public function getSrcUrl()
    {
        /*if (($this->site->name ?? null) == AvailableSite::SITE_SHEIN) {
            return 'https://ad.admitad.com/g/1kjlqr06u0bb7d75e46bf0af71e07a/?ulp=' . urlencode($this->src_product_url);
        }*/
        return $this->src_product_url;
    }

    public function getSiteName()
    {
        $siteName = $this->site->name ?? '';
        if (!$siteName) {
            Yii::error($this->attributes, 'MissingSiteName');
        }
        return ucfirst(str_replace('.com', '', $siteName));
    }

    public function getShopifyEditUrl()
    {
        $user = $this->getUserModel();
        $editUrl = "{$user->shopUrl}/admin/products/$this->shopify_id";
        return $editUrl;
    }

    /**
     * @return string
     */
    public function getDisplayPrice(): string
    {
        $user = $this->getUserModel();
        $shopifyData = json_decode($user->shopify_details, true);
        $data = json_decode($this->product_data, true);
        $moneyString = $shopifyData['moneyFormat'] ?? '${{amount}}';

        if ($this->currency && $this->currency->code !== Currency::DEFAULT_CURRENCY) {
            $moneyString = $this->currency->code . "({$this->currency->name}) " . '{{amount}}';
        }

        $price = str_replace(['{{amount}}', '{{amount_with_comma_separator}}'], $data['price'], $moneyString);
        return $price;
    }

    public function getProductReviews()
    {
        return $this->hasMany(ProductReview::class, ['product_id' => 'id']);
    }

    public function addToPublishQueue()
    {

        if (!$this->id) {
            throw new InvalidArgumentException('First you need save product in db');
        }
        $publishQueue = ProductPublishQueue::findOne(['product_id' => $this->id]) ?? new ProductPublishQueue();
        $publishQueue->product_id = $this->id;
        $publishQueue->status = ProductPublishQueue::STATUS_PENDING;
        $publishQueue->save();

        return Yii::$app->queue->push(new PublishJob([
            'id' => $publishQueue->id
        ]));
    }

    public function getPreviewUrl()
    {
        return '/product/' . $this->id; // need configure console url manager
    }

    /**
     * @return ActiveQuery
     */
    public function getRecommendedProduct(): ActiveQuery
    {
        return $this->hasOne(RecommendedProduct::class, ['sku' => 'sku']);
    }

    public function getReviewsCount(): int
    {
        return $this->getProductReviews()->count();
    }

    public function updateProductType()
    {
        $productData = json_decode($this->product_data, true);
        $productTypeName = $productData['product_type'] ?? null;
        if ($productTypeName) {
            $productType = ProductType::findOne(['name' => $productTypeName]);
            if ($productType) {
                $this->product_type_id = $productType->id;
            } else {
                $newProductType = new ProductType();
                $newProductType->name = $productTypeName;
                $newProductType->save();
                $this->product_type_id = $newProductType->id;
            }

            $this->product_type = $productTypeName;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductPriceMarkup()
    {
        return $this->hasOne(ProductPriceMarkup::class, ['product_id' => 'id']);
    }

    public function getReviewsUrl(): string
    {
        return Url::toRoute(['/product-review', 'product_id' => $this->id]);
    }

    public function setPriceMarkup()
    {
        $productData = json_decode($this->product_data, true);
        $productPriceMarkup = new ProductPriceMarkup();
        $productPriceMarkup->product_id = $this->id;
        $productPriceMarkup->price_markup = $productData['price_markup'];
        $productPriceMarkup->price_by_percent = $productData['priceByPercent'];
        $productPriceMarkup->price_by_amount = $productData['priceByAmount'];
        $productPriceMarkup->compare_at_price_markup = $productData['compareAtPrice_markup'];
        $productPriceMarkup->compare_at_price_by_amount = $productData['compareAtPriceByAmount'];
        $productPriceMarkup->compare_at_price_by_percent = $productData['compareAtPriceByPercent'];

        if ($productPriceMarkup->save()) {
            unset($productData['price_markup']);
            unset($productData['priceByPercent']);
            unset($productData['priceByAmount']);
            unset($productData['compareAtPrice_markup']);
            unset($productData['compareAtPriceByPercent']);
            unset($productData['compareAtPriceByAmount']);
            $this->product_data = json_encode($productData);

            $this->save();
        } else {
            Yii::error($productPriceMarkup->getErrors(), 'PriceMarkupSaveError');
        }



    }


    public function getRate(): float
    {
        return round($this->getProductReviews()->where(['status' => ProductReview::STATUS_PUBLISHED])->average('rate'), 1);
    }
}
