<?php

namespace common\models;

use backend\helpers\CurrencyHelper;
use common\helpers\CalculationHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Slince\Shopify\Model\Products\Variant;
use Throwable;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%categories}}".
 *
 * @property int $id
 * @property int $product_id
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property boolean $import_reviews
 * @property string $error_msg
 * @property string $content
 *
 * @property Product $product
 * @property MonitoringQueue[] $notMonitoredQueues
 * @property ImportQueue $importQueue
 */
class MonitoringQueue extends \yii\db\ActiveRecord
{

    const STATUS_PENDING = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_ERROR = 2;

    const MONITORING_QUEUE_STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_SUCCESS => 'Success',
        self::STATUS_ERROR => 'Error',
    ];

    const SYNC_FIELDS = [
        'price' => 'price',
        'compare_at_price' => 'compare_at_price',
        'inventory_quantity' => 'inventory_quantity'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%monitoring_queues}}';
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
            [['import_reviews'], 'boolean'],
            [['error_msg'], 'string'],
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
            'error_msg' => 'Error Message',
            'import_reviews' => 'Import Reviews',
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
     * Gets query for [[ImportQueue]].
     *
     * @return ActiveQuery
     */
    public function getImportQueue(): ActiveQuery
    {
        return $this->hasOne(ImportQueue::class, ['monitoring_queue_id' => 'id']);
    }

    public function monitorQueues($pageContent = null, $fromExtension = false)
    {
        $model = new ProductUrl();
        $model->setUser($this->product->user);
        Yii::$app->params['user_id'] = $this->product->user_id;
        $productUrl = $this->product->src_product_url ?? null;
        if ($productUrl) {
            $model->url = $productUrl;
            try {
                if ($this->product->is_published && $this->product->shopify_id) {
                    $client = $this->product->user->graphQlClient;
                    $shopifyVariants = $client->getProduct( $this->product->shopify_id)['variants'];
                    $shopifyVariantIds = array_map(function (array $shopifyVariant) {
                        return $shopifyVariant['id'];
                    }, $shopifyVariants);

                    ProductVariant::deleteAll(['AND',
                        ['=', 'product_id', $this->product->id],
                        ['NOT IN', 'shopify_variant_id', $shopifyVariantIds]
                    ]);
                }


                $includeReviews = !empty($this->product->monitoring_reviews);
                $pageContent = $pageContent ?? $model->getPageContent($includeReviews, true, $this->id);

                if ($pageContent === false) {
                    Yii::error($model->url, 'MonitoringSheinNoContent');
                    sleep(3);
                    $pageContent = $model->getPageContent($includeReviews, true, $this->id);
                    if ($pageContent) {
                        Yii::error($model->url, 'MonitoringSheinContentFromRetry');
                    } else {
                        Yii::error($model->url, 'MonitoringSheinNoContentFromRetry');
                    }
                }

                $this->content = json_encode($pageContent, JSON_UNESCAPED_UNICODE);
                $this->save();

                if ((isset($pageContent['status']) && $pageContent['status'] == 404)) {
                    $variantsData = [];
                } else {
                    $response = $model->getVariantDataByContent($pageContent ?? '', $fromExtension);
                    $data = json_decode($response, true);
                    $variants = $data['data']['variants'] ?? [];
                    $variantsData = ArrayHelper::index($variants, 'default_sku');
                    $reviewsData = $pageContent['reviews'] ?? [];
                }
            } catch (ClientException $exception) {

                if ($exception->getResponse()->getStatusCode() != 404) {
                    Yii::error($exception->getMessage(), 'MonitorError');
                    $this->error_msg = $exception->getMessage();
                    $this->status = self::STATUS_ERROR;
                    $this->save();
                    return false;
                }
                $variantsData = [];
            } catch (Throwable $exception) {
                Yii::error($exception->getMessage(), 'MonitorError');
                $this->error_msg = $exception->getMessage();
                $this->status = self::STATUS_ERROR;
                $this->save();
                return false;
            }
            /* @var ProductVariant[] $dbProductVariants */
            $dbProductVariants = $this->product->getProductVariants()->all();
            $exist = !count($variantsData) || $this->skusExists($dbProductVariants, $variantsData);

            $recommendedProduct = $this->product->recommendedProduct;

            if (!$this->product->product_type_id && !empty($pageContent['product_type'])) {
                $productType = ProductType::findOne(['name' => $pageContent['product_type']]);

                if ($productType) {
                    $this->product->product_type_id = $productType->id;
                } else {
                    $newProductType = new ProductType();
                    $newProductType->name = $pageContent['product_type'];
                    $newProductType->save();
                    $this->product->product_type_id = $newProductType->id;
                }

                $this->product->save();
            }


            if ($recommendedProduct) {
                $recommendedProduct->product_data = json_encode($pageContent, JSON_UNESCAPED_UNICODE);

                if (!$recommendedProduct->product_type_id) {
                    $recommendedProduct->product_type_id = $this->product->product_type_id;
                }

                $recommendedProduct->save();
            }

            if (!$exist) {
                Yii::error(['id' => $this->product->id, 'dbProducts' => $dbProductVariants, 'variantsData' => $variantsData], 'MonitoringErrorSkuNotExists');
                $this->error_msg = 'SKU not exists ' . json_encode([$dbProductVariants, $variantsData]);
                $this->status = self::STATUS_ERROR;
                $this->save();
                return false;
            }

            $updatedVariants = [];
            try {

                if ($this->product->site_id == 24) { // trendyol
                    if (count($dbProductVariants) == count($variantsData)) {
                        $i = 0;
                        foreach ($variantsData as $sku => $variantData) {
                            $dbProductVariants[$i]->default_sku = $sku;
                            $dbProductVariants[$i]->save();
                            $i++;
                        }
                    }
                }

                foreach ($dbProductVariants as $dbVariant) {
                    /* @var ProductVariant $dbVariant */
                    if ($this->syncVariant($dbVariant, $variantsData, $fromExtension) && $dbVariant->shopify_variant_id) {
                        $updatedVariants[$dbVariant->shopify_variant_id] = true;
                    }
                }

                if ($updatedVariants && $this->product->is_published) {
                    if (!isset($productDataModel)) {
                        $productDataModel = $this->product->getProductDataModel();
                    }
                    if ($this->product->monitoring_price) {
                        $productDataModel->setVariantPrices();
                    }
                    if ($this->product->monitoring_stock) {
                        $productDataModel->updateShopifyVariantsInventory($updatedVariants);
                    }
                }
    
                if (!empty($reviewsData) && $this->product->monitoring_reviews) {
                    $productDataModel = $this->product->getProductDataModel();
                    $productDataModel->reviews = $reviewsData;
                    $productDataModel->createProductReviews($this->product);
                }
                
                $this->product->monitored_at = time();
                $this->product->save();
                return true;
            } catch (\Exception $exception) {
                Yii::error([$exception->getMessage(), $this->product_id, $this->product->src_product_url], 'MonitorError2');
                Yii::error(['file' => $exception->getFile(), 'line' => $exception->getLine()], 'MonitoringErrorTrace');
                $this->error_msg = $exception->getMessage();
                $this->status = self::STATUS_ERROR;
                $this->save();
                return false;
            }

        }
        return false;
    }

    private function skusExists($dbProductVariants, $variantsData): bool
    {
        $dbVariantsSkus = ArrayHelper::getColumn($dbProductVariants, 'default_sku');
        $variantDataSkus = array_keys($variantsData);

        foreach ($variantDataSkus as $sku) {
            foreach ($dbVariantsSkus as $dbSku) {
                if (strpos($dbSku, $sku) !== false || strpos($sku, $dbSku) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    public function syncVariant(ProductVariant $variant, array $variantsData): bool
    {
        $variantHasMultipartSku = strpos($variant->default_sku, '--shioni-') !== false;

        if ($variantsData && $variant->product->site_id = 1 && count(explode('--shioni-', array_keys($variantsData)[0])) > 1 && !$variantHasMultipartSku) {
            $skuData = [];

            foreach (array_keys($variantsData) as $array_key) {
                $explodedData = explode('--shioni-', $array_key);
                $skuData[] = $explodedData[0];
                $skuData[] = $explodedData[1];
            }
        }

        $externalStatement = false;
        if ($variantHasMultipartSku && count(explode('--shioni-', array_keys($variantsData)[0])) < 2) {
            $dbVariantSkuArr = explode('--shioni-', $variant->default_sku);
            $externalStatement = !isset($variantsData[$dbVariantSkuArr[0]]) && !isset($variantsData[$dbVariantSkuArr[1]]);
        }

        /* @var $variant ProductVariant */
        if (
            (!isset($skuData) && !isset($variantsData[$variant->default_sku]) && !$variantHasMultipartSku) ||
            (isset($skuData) && !in_array($variant->default_sku, $skuData)) ||
            $externalStatement
        ) {
            if ($variant->inventory_quantity && $this->product->monitoring_stock) {
                $variant->inventory_quantity = 0;
            }
        } else {
            if (!$this->product->productPriceMarkup) {
                $this->product->setPriceMarkup();
                unset($this->product->productPriceMarkup);
            }

            if (is_null($this->product->currency_rate)) {
                $defaultCurrency = Currency::getDb()->cache(function ($db) {
                    return Currency::findOne(['code' => Currency::DEFAULT_CURRENCY]);
                }, 1000);
                $this->product->currency_rate = 1;
                $this->product->currency_id = $defaultCurrency->id;
                $this->product->default_currency_id = $defaultCurrency->id;
                $this->product->save();
            }

            if (!isset($skuData)) {
               if ($variantHasMultipartSku && !isset($variantsData[$variant->default_sku])) {
                  $variantData = $variantsData[$dbVariantSkuArr[0]] ?? $variantsData[$dbVariantSkuArr[1]];
               }else {
                  $variantData =  $variantsData[$variant->default_sku];
               }
            } else {
                foreach(array_keys($variantsData) as $value) {
                    if (
                        (strpos($value, $variant->default_sku) !== false) ||
                        ($variantHasMultipartSku && (strpos($value, $variantsData[$dbVariantSkuArr[0]]) !== false || strpos($value, $variantsData[$dbVariantSkuArr[1]]) !== false))
                    ) {
                        $variantData = $variantsData[$value];
                    }
                }
            }

            $productData = $this->product->getProductData();
            $variantPriceMarkup = $variant->variantPriceMarkup;
            $productPriceMarkup = $this->product->productPriceMarkup;
            $productData['currency_id'] = $this->product->currency_id;
            $productData['default_currency_id'] = $this->product->default_currency_id;
            $productData['price_markup'] = $variantPriceMarkup ? $variantPriceMarkup->price_markup : $productPriceMarkup->price_markup;
            $productData['priceByAmount'] = $variantPriceMarkup ? $variantPriceMarkup->price_by_amount : $productPriceMarkup->price_by_amount;
            $productData['priceByPercent'] = $variantPriceMarkup ? $variantPriceMarkup->price_by_percent : $productPriceMarkup->price_by_percent;
            $productData['compareAtPrice_markup'] = $variantPriceMarkup ? $variantPriceMarkup->compare_at_price_markup : $productPriceMarkup->compare_at_price_markup;
            $productData['compareAtPriceByAmount'] = $variantPriceMarkup ? $variantPriceMarkup->compare_at_price_by_amount : $productPriceMarkup->compare_at_price_by_amount;
            $productData['compareAtPriceByPercent'] = $variantPriceMarkup ? $variantPriceMarkup->compare_at_price_by_percent : $productPriceMarkup->compare_at_price_by_percent;
            $rate = 1;
            if ($variantData['price']) {
                $planFeatures = Feature::getPlanSettingkeys($variant->product->user);
                if (isset($planFeatures['product_currency_convertor']) && $planFeatures['product_currency_convertor']) {
                    if ($variant->product->update_currency_rate && $this->product->monitoring_price) {
                        $rate = $this->getCurrencyRate($productData['default_currency_id'], $productData['currency_id']);
                        $decimalPlaces = abs(floor(log10(abs($rate))));
                        $formattedRate = sprintf('%.' . ($decimalPlaces + 1) . 'f', $rate);

                        $variant->product->currency_rate = $formattedRate;
                        $variant->product->save();
                    } else {
                        $rate = $variant->product->currency_rate ?? 1;
                    }
                }

                $variantData['price'] = $this->getPrice($productData['price_markup'], $variantData['price'], $productData['priceByAmount'], $productData['priceByPercent'], $rate);
                $variantData['compare_at_price'] = $this->getPrice($productData['compareAtPrice_markup'], $variantData['price'] / $rate, $productData['compareAtPriceByAmount'], $productData['compareAtPriceByPercent'], $rate);
            }

            $syncFields = static::SYNC_FIELDS;

            if (!$this->product->monitoring_stock) {
                unset($syncFields['inventory_quantity']);
            }

            if (!$this->product->monitoring_price) {
                unset($syncFields['price']);
                unset($syncFields['compare_at_price']);
            }

            foreach ($syncFields as $field) {
                $variant->$field = $variantData[$field];
            }
        }
        $isUpdated = $this->saveVariantChanges($variant);
        $variant->save();
        return $isUpdated;
    }


    public function saveVariantChanges(ProductVariant $updatedVariant): bool
    {
        $variantChange = new VariantChange();
        $variantChange->variant_id = $updatedVariant->id;
        $isUpdated = false;
        foreach (static::SYNC_FIELDS as $field) {
            $old = 'old_' . $field;
            $new = 'new_' . $field;
            $variantChange->$old = $updatedVariant->getOldAttribute($field);
            $variantChange->$new = $updatedVariant->$field;
            if ($variantChange->$old !== $variantChange->$new) {
                $isUpdated = true;
            }
        }
        if ($isUpdated) {
            $variantChange->save();
        }
        return $isUpdated;
    }

    public function getPrice($markup, $defaultPrice, $addedPrice, $addedPricePercent, $rate): float
    {
        $addedPrice = floatval($addedPrice);
        $addedPricePercent = floatval($addedPricePercent);
        $defaultPrice = floatval($defaultPrice) * $rate;
        if ($markup) {
            return round($defaultPrice + $addedPrice, 2);
        } else {
            return round(CalculationHelper::getTotalByPercent(floatval($defaultPrice), floatval($addedPricePercent)), 2);
        }
    }

    public function getCurrencyRate($from, $to)
    {
        $fromCurrency = Currency::getDb()->cache(function ($db) use($from) {
            return Currency::findOne(['id' => $from]);
        }, 1000);
        $toCurrency = Currency::getDb()->cache(function ($db) use($to) {
            return Currency::findOne(['id' => $to]);
        }, 1000);;
        $fromRate = $fromCurrency->code !== Currency::DEFAULT_CURRENCY ? CurrencyHelper::convertToUSD($fromCurrency->code, 1) : 1;
        $toRate = $toCurrency->code !== Currency::DEFAULT_CURRENCY ? CurrencyHelper::convertToUSD($toCurrency->code, 1) : 1;

        return $fromRate / $toRate;
    }


}
