<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 21.05.2019
 * Time: 11:33
 */

namespace frontend\models\api;

use common\models\{AvailableSite,
    Notification,
    Product,
    ProductBulkImportItem,
    ProductPriceMarkup,
    ProductReviewImage,
    ProductType,
    ProductUrl,
    ProductVariant,
    User,
    UserSetting,
    VariantPriceMarkup};
use common\helpers\TextHelper;
use common\services\ShopifyGraphQLService;
use common\validators\NoEmojiValidator;
use frontend\models\RecommendedProduct;
use GuzzleHttp\Exception\ClientException;
use Yii;
use yii\base\BaseObject;
use yii\base\Model;

/**
 *
 * @property int $id
 * @property string $title
 * @property string $body_html
 * @property string $vendor
 * @property string $product_type
 * @property string $variants
 * @property string $options
 * @property int $productId
 * @property int $stockCount
 * @property array $reviews
 * @property string $productUrl
 * @property string $brand
 * @property double $weight
 * @property string $weight_unit
 * @property double $price
 * @property double $compare_at_price
 * @property double $avgReview
 * @property string $collection
 * @property string $images
 * @property int $price_markup
 * @property int $compareAtPrice_markup
 * @property double $priceByPercent
 * @property double $priceByAmount
 * @property int $publish
 * @property double $compareAtPriceByAmount
 * @property double $compareAtPriceByPercent
 * @property $client
 *
 * @property Product $product
 * @property  $response
 *
 *
 */
class ProductData extends Model
{
    public $title;
    public $body_html;
    public $sizeTable;
    public $vendor;
    public $product_type;
    public $variants;
    public $options;
    public $productId;
    public $stockCount;
    public $reviews;
    public $productUrl;
    public $brand;
    public $weight;
    public $weight_unit;
    public $price;
    public $compare_at_price;
    public $avgReview;
    public $collection;
    public $new_collection_name;
    public $images;
    public $price_markup;
    public $compareAtPrice_markup;
    public $priceByPercent;
    public $priceByAmount;
    public $compareAtPriceByAmount;
    public $compareAtPriceByPercent;
    public $client;
    public $publish;
    public $currency_id;
    public $default_currency_id;
    public $currency_rate;
    public $imported_from;
    public $isFromQueue;
    public $multiple_import;
    public $bulk_import;

    protected $response;


    protected $product;
    protected $productPriceMarkup;

    public static function getAddress($address)
    {
        $orderAddress = [
            'address_firstName' => $address['firstName'],
            'address_lastName' => $address['lastName'],
            'address_company' => $address['company'],
            'address_address1' =>  $address['address1'],
            'address_address2' => $address['address2'],
            'address_city' => $address['city'],
            'address_province' => $address['province'],
            'address_country' => $address['country'],
            'address_zip' => $address['zip'],
            'address_phone' => $address['phone'],
            'address_name' => $address['name'],
            'address_provinceCode' => $address['provinceCode'],
            'address_countryCode' => $address['countryCode'],
            'address_countryNam' => $address['country'],
        ];

        return $orderAddress;

    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'body_html', 'productId', 'productUrl', 'price', 'compare_at_price', 'avgReview',
                'images', 'price_markup', 'compareAtPrice_markup', 'priceByPercent', 'priceByAmount',
                'compareAtPriceByAmount', 'compareAtPriceByPercent', 'sizeTable', 'new_collection_name', 'currency_id', 'default_currency_id', 'currency_rate'], 'safe'],
            [['productId'], 'required'],
            [['productId', 'stockCount', 'price_markup', 'compareAtPrice_markup', 'avgReview', 'publish', 'imported_from'], 'safe'],
            [['title', 'body_html', 'vendor', 'product_type', 'variants', 'options', 'reviews', 'productUrl',
                'brand', 'weight_unit', 'collection', 'images', 'multiple_import', 'bulk_import'], 'safe'],
            [['weight', 'price', 'compare_at_price', 'avgReview', 'priceByPercent', 'priceByAmount',
                'compareAtPriceByAmount', 'compareAtPriceByPercent', 'currency_id', 'default_currency_id', 'currency_rate'], 'number'],
            [['title', 'body_html', 'vendor', 'product_type'], NoEmojiValidator::class]
        ];
    }

    protected function saveShopifyInfoToProduct()
    {
        $productId = $this->response['id'];
        $product = $this->getProduct();
        $product->shopify_id = $this->response['id'];
        $product->user_id = 10;
        $product->handle = $this->response['handle'];
        $product->is_published = Product::PRODUCT_IS_PUBLISHED;
        $product->save();

        $this->setVariantIds();
        $this->setVariantImages();
        $this->setVariantPrices();
//        if ($product->user_id == 151) {
//            $this->updateLocations('64553353407');
//        }

        $this->createNotification($product);

        if (!empty($this->collection)) {
            try {
                (new ShopifyGraphQLService($this->client))->addProductToCollection($this->collection, [$productId]);
            } catch (\Exception $exception) {
            }
        }
    }

    public function createShopifyProduct()
    {
        $productData = $this->getProductData();
        $productResponse = (new ShopifyGraphQLService($this->client))->createProduct($productData);
        $this->response = $productResponse['productCreate']['product'];
        $this->response['variants'] =  (new ShopifyGraphQLService($this->client))->createProductVariants(['productId' => $this->response['id'], 'variants' => $this->getGraphQlVariants($this->variants)]);
        $this->response['images'] = (new ShopifyGraphQLService($this->client))->addProductMedia(['productId' => $this->response['id'], 'media' => $this->getImages()]);

        $this->saveShopifyInfoToProduct();
        return $this->response;
    }

    public function getGraphQlVariants(array $variants, $productId = null)
    {
        $data = [];

        foreach ($variants as $key => $variant) {
            if ($key > 98) {
                break;
            }
            $options = [];
            if (!empty($variant['option1']) && !empty($this->options[0]['name'])) {
                $options[] = ['name' => $variant['option1'], 'optionName' => $this->options[0]['name']];
            }
            if (!empty($variant['option2'])) {
                $options[] = ['name' => $variant['option2'], 'optionName' => $this->options[1]['name']];
            }
            if (!empty($variant['option3']) || isset($this->options[2])) {
                $options[] = ['name' => !empty($variant['option3']) ? $variant['option3'] :  $this->options[2]['values'][0], 'optionName' => $this->options[2]['name']];
            }
            $variantData = [
                'price' => $variant['price'],
                'compareAtPrice' => $variant['compare_at_price'],
                'inventoryPolicy' => 'DENY',
                'inventoryQuantities' => [
                    'availableQuantity' => $variant['inventory_quantity'],
//                    'locationId' => 'gid://shopify/Location/' . json_decode($this->product->user->shopify_details, true)['primaryLocationId'],
                    'locationId' => 'gid://shopify/Location/77327761605',
                ],
            ];

            if (!empty($options)) {
                $variantData['optionValues'] = $options;
            } else {
                $variantData['optionValues'] = [["name" => "Default Title2", "optionName" => 'Title']];
            }

            $data[] = $variantData;
        }

        return $data;
    }
    public function setVariantIds()
    {
        $dbVariants = $this->product->getProductVariants()->all();
        $shopifyVariants = $this->response['variants'];
        foreach ($dbVariants as $key => $dbVariant) {

            $shopifyVariant = $shopifyVariants[$key] ?? null;
            if (!$shopifyVariant) {
                Yii::error([$this->product->id, $key, $shopifyVariants], 'EmptySetVariantIds');
                continue;
            }
            $dbVariant->inventory_item_id = $shopifyVariant['inventoryItem']['id'];
            $dbVariant->shopify_variant_id = $shopifyVariant['id'];
            $dbVariant->save();
        }
    }

    public function createNotification(Product $product, $error = false)
    {
        $notification = new Notification();
        $notification->user_id = $product->user_id;
        $notification->subject = $error === false ? 'Product has been imported #'.$product->shopify_id
            : 'Error: product #'.$product->id . '<br>'. $error;
        $notification->text =  $product->title . ' successfully published to Shopify';
        $notification->url =  $product->getPreviewUrl();
        $notification->notification_type = $error === false ?  Notification::SUCCESS_NOTIFICATION : Notification::DANGER_NOTIFICATION;
        $notification->additional_data =  $error === false ? json_encode([
            'id' => $product->id,
            'url' => $product->getShopifyEditUrl(),
            'handle' => $product->getHandleUrl(),
        ]) : null ;
        $notification->save();
    }

    public function getProductData(): array
    {
        if ($this->sizeTable) {
            $this->body_html .= '<br>' . $this->sizeTable;
        }

        if (Yii::$app->params['enableReview']) {
            $reviewShortCode = '[shionimporter-reviews]';
            $reviewHtml = "<div style='display: none' id='shionimporter-reviews'></div>";
            $this->body_html = strpos($this->body_html, $reviewShortCode) ? $this->body_html : $this->body_html . $reviewHtml;
        }


        if (!$this->variants) {
            $productVariant = new ProductVariant(['price' => $this->price]);
            $cost = $productVariant->calculateCost($this->price_markup, $this->priceByAmount, $this->priceByPercent);
            $this->options = [
                [
                    'name' => 'Title',
                    'values' => ['Default Title']
                ]
            ];
            $this->variants = [
                [
                    'option1' => 'Default Title',
                    'sku' => $this->productId,
                    'price' => $this->price,
                    'cost' => $cost,
                    'compare_at_price' => $this->compare_at_price,
                    'inventory_quantity' => $this->stockCount,
                ]
            ];
        }

        foreach ($this->variants as &$variant) {
            $variant['inventory_policy'] = 'deny';
            $variant['inventory_management'] = 'shopify';
        }

        $data = [
            'title' => TextHelper::decodeText($this->title),
            'descriptionHtml' => $this->body_html,
            'vendor' => $this->brand,
            'productType' => $this->product_type,
        ];

        $options = $this->getGraphQlOptions($this->options);
        if (!empty($options)) {
            $data['productOptions'] = $options;
        } else {
            $data['productOptions'] = [["name" => "Title", "values" => ['name' => '1'], 'position' => 1]];
        }

        if ($this->collection) {
            $data['collectionsToJoin'] = $this->collection;
        }

        return $data;
    }

    public function setImgHttps(?string $src)
    {
        if ($src && $src[0] == '/') {
            $src = 'https:' . $src;
        }
        return $src;
    }

    public function getImages()
    {
        $existImages = [];
        $images = [];
        if (!empty($this->images)) {
            foreach ($this->images as $image) {
                $src = $this->setImgHttps($image);
                if (!in_array($src, $existImages)) {
                    $existImages[] = $src;
                    $images[] = [
                        'originalSource' => $src,
                        'mediaContentType' => 'IMAGE'
                    ];
                }
            }
        }
        if (!empty($this->variants)) {
            foreach ($this->variants as $variant) {
                if (!empty($variant['img'])) {
                    $src = $this->setImgHttps($variant['img']);
                    if (!in_array($src, $existImages)) {
                        $existImages[] = $src;
                        $images[] = [
                            'src' => $src
                        ];
                    }
                }
            }
        }

        return $images;
    }

    public function setVariantImages()
    {

        $variantIds = [];
        $images = $this->response['images'];

        foreach ($this->response['variants'] as $key => $variant) {
            $imgId = null;
            foreach ($this->getImages() as $imgKey => $img) {
                if (!empty($images[$imgKey]) && !empty($this->variants[$key]['img']) && $this->setImgHttps($this->variants[$key]['img']) == $img['src']) {
                    $img = $images[$imgKey];
                    $imgId = $img['id'];
                    break;
                }
            }
            $variantIds[] = [
                'id' => $variant['id'],
                'mediaId' => $imgId,
            ];
        }

        return (new ShopifyGraphQLService($this->client))->updateProductVariants($this->product->shopify_id, $variantIds);
    }

    public function updateDescription()
    {
        $variables = [
            "input" => [
                "id" => $this->product->shopify_id,
                "body_html" => $this->body_html,
            ],
        ];
        (new ShopifyGraphQLService($this->client))->updateProduct($variables);
    }

    public function setVariantPrices()
    {
        $variantsUpdateData = [];
        foreach ($this->product->productVariants as $productVariant) {
            if ($productVariant->shopify_variant_id && $productVariant->price > 0) {
                $variantsUpdateData[] = [
                    'id' => $productVariant->shopify_variant_id,
                    'price' => $productVariant->price,
                    'compare_at_price' => $productVariant->compare_at_price
                ];
            }
        }

        try {
            if (!empty($variantsUpdateData)) {
                (new ShopifyGraphQLService($this->client))->updateProductVariants($this->product->shopify_id, $variantsUpdateData);
            }
        } catch (\Exception $exception) {
            Yii::error(['message' => $exception->getMessage(), 'id' => $this->product->id], 'SetVariantPrices');
        }
    }

    public function updateShopifyVariant(ProductVariant $productVariant)
    {
      try {
          return (new ShopifyGraphQLService($this->client))->updateProductVariants($productVariant->shopify_variant_id, [
              'price' => $productVariant->price,
              'compare_at_price' => $productVariant->compare_at_price,
              'sku' => $productVariant->sku,
              'option1' => $productVariant->option1,
              'option2' => $productVariant->option2,
              'option3' => $productVariant->option3,
          ]);
      } catch (\Exception $exception) {
        Yii::error(['message' => $exception->getMessage(), 'id' => $this->product->id], 'UpdateShopifyVariant');
        return $exception;
      }
    }

    public function updateShopifyVariantImage(ProductVariant $productVariant)
    {
        try {
            return (new ShopifyGraphQLService($this->client))->updateProductVariants($productVariant->shopify_variant_id, ['image' => ['src' => $productVariant->img]]);
        } catch (\Exception $exception) {
            Yii::error(['message' => $exception->getMessage(), 'id' => $this->product->id], 'UpdateShopifyVariantImage');
            return $exception;
        }
    }

    public static function getGraphqlApiId($response)
    {
        $pos = strripos($response->getAdminGraphqlApiId(), '/');
        $id = substr($response->getAdminGraphqlApiId(), $pos + 1);
        return $id;

    }

    protected function getGraphQlOptions(array $options): array
    {
        $data = [];

        foreach ($options as $key => $option) {
            $optionObj = ['name' => $option['name'], 'values'=>[], 'position' => $key + 1];
            $optionObj['values'] = ['name' => '1'];
            $data[] = $optionObj;
        }
        return $data;
    }

    public function setProduct(Product $product)
    {
        $this->product = $product;
    }

    public function getProduct()
    {
        if (!$this->product) {
            $this->product = new Product();
        }
        return $this->product;
    }

    public function getProductPriceMarkupObject()
    {
        if (!$this->productPriceMarkup) {
            $this->productPriceMarkup = new ProductPriceMarkup();
        }
        return $this->productPriceMarkup;
    }

    public function updateLocations($locationId)
    {
        foreach ($this->product->productVariants as $dbVariant) {
            $updateData = [
                'inventory_item_id' => $dbVariant->inventory_item_id,
                'location_id' => $locationId,
                'available' => $dbVariant->inventory_quantity
            ];
            try {
                $this->client->getInventoryLevelManager()->set($updateData);
                usleep(40);
            } catch (\Exception $exception) {
                Yii::error([
                    'error' => $exception->getMessage(),
                    'id' => $this->product->id,
                    'data' => $updateData
                ], 'updateLocationsInventory');
            }
        
        }
    }

    /**
     * @param User $user
     * @return Product|null
     */
    public function saveProduct(User $user)
    {

        $productData = $this->attributes;
        unset($productData['client']);
        $productPriceMarkupData = [
          'price_markup' => $productData['price_markup'],
          'compare_at_price_markup' => $productData['compareAtPrice_markup'],
          'price_by_percent' => $productData['priceByPercent'],
          'price_by_amount' => $productData['priceByAmount'],
          'compare_at_price_by_amount' => $productData['compareAtPriceByAmount'],
          'compare_at_price_by_percent' => $productData['compareAtPriceByPercent'],
        ];

        unset($productData['price_markup']);
        unset($productData['compareAtPrice_markup']);
        unset($productData['priceByPercent']);
        unset($productData['priceByAmount']);
        unset($productData['compareAtPriceByAmount']);
        unset($productData['compareAtPriceByPercent']);

        $productData['reviews'] = [];// @todo check
        $product = $this->getProduct();

        $product->title = TextHelper::decodeText($this->title);
        $product->user_id = $user->id;
        $product->src_product_url = explode('?', $this->productUrl)[0];
        if ($product->src_product_url && strpos($product->src_product_url, 'http') === false) {
            $product->src_product_url = 'https://'.$product->src_product_url;
        }
        if (strlen($product->src_product_url) > 600) {
            $product->src_product_url = urldecode($product->src_product_url);
        }
        $product->site_id = $this->getSiteId($product->src_product_url);
        $product->sku = (string)$this->productId;
        if ($this->isFromQueue && !empty($productData['body_html'])) {
            $productData['body_html'] = $this->processBodyHtmlForQueue($productData['body_html']);
        }

        $product->product_data = json_encode($productData);
        $product->count_variants = count($this->variants);
        $product->is_published = $product->is_published ?? Product::PRODUCT_IS_NOT_PUBLISHED;
        $product->currency_id = $this->currency_id;
        $product->default_currency_id = $this->default_currency_id;
        $product->currency_rate = $this->currency_rate;
        $product->imported_from = $this->imported_from ?? Product::IMPORTED_FROM_EXTENSION;
        $product->updateProductType();

        if (!$product->save()) {
            Yii::error(['errors' => $product->getErrors(), 'attributes' => $product->getAttributes()], 'ProductSaveError');
            return null;
        }

        $productPriceMarkupData['product_id'] = $product->id;
        $productPriceMarkup = $this->getProductPriceMarkupObject();

        foreach($productPriceMarkupData as $key => $value) {
          $productPriceMarkup->setAttribute($key, $value);
        }

        if (!$productPriceMarkup->save()) {
          Yii::error(['errors' => $productPriceMarkup->getErrors(), 'attributes' => $productPriceMarkup->getAttributes()], 'ProductSaveError');
          return null;
        }

		$recommendedProduct = $product->recommendedProduct;
		if ($recommendedProduct) {
			$recommendedProduct->total ++;
		} else {
			$recommendedProduct = new RecommendedProduct([
				'sku' => $product->sku,
				'site_id' => $product->site_id,
				'total' => 1,
				'title' => $product->title,
				'image' => $product->getProductImage(),
				'url' => $product->src_product_url,
			]);
		}
		$recommendedProduct->save();
        $this->saveVariants($this->variants, $product->id);
        $this->createProductReviews($product);
        $this->product = $product;
        if ($this->bulk_import) {
            $bulkId = $this->bulk_import;
            $bulkImportItem = ProductBulkImportItem::find()
                ->joinWith(['bulkImport' => function($query) use ($user, $bulkId){
                    $query->andWhere(['user_id' => $user->id]);
                    $query->andWhere(['bulk_imports.id' => $bulkId]);
                }])
                ->andWhere(['product_bulk_import_items.status' => 0])
                ->andWhere(['LIKE', 'product_bulk_import_items.url' , '%' . $product->src_product_url . '%', false])
                ->one();

            if ($bulkImportItem) {
                $bulkImportItem->product_id = $product->id;
                $bulkImportItem->status = ProductBulkImportItem::STATUS_SUCCESS;
                $bulkImportItem->url = $product->src_product_url;
                if ($bulkImportItem->save()) {
                    $this->publish = true; // @todo get from other place
                } else {
                    echo "<pre>aaa";
                    print_r([$bulkImportItem->getErrors(), $bulkImportItem->getAttributes()]);die;
                }
            }
        }

        return $product;
    }

    public function decodeText(string $text, int $count = 0): string
    {
        if ($text !== urldecode($text) && $count < 5) {
            return $this->decodeText(urldecode($text), ++$count);
        }

        return $text;
    }

    public function saveVariants($variants, $productId)
    {
        foreach ($variants as $variant) {
            $variant['product_id'] = $productId;
            $productVariant = new ProductVariant();
            if ($productVariant->load($variant, '') && $productVariant->validate()) {
                $productVariant->inventory_quantity = $productVariant->inventory_quantity ?? $this->stockCount;
                $productVariant->save();
                if (isset($variant['changed'])) {
                  $variantPriceMarkup = new VariantPriceMarkup();
                  $variantPriceMarkup->price_by_amount = $variant['price_by_amount'];
                  $variantPriceMarkup->price_by_percent = $variant['price_by_percent'];
                  $variantPriceMarkup->price_markup = $variant['price_markup'];
                  $variantPriceMarkup->compare_at_price_by_amount = $variant['compare_at_price_by_amount'];
                  $variantPriceMarkup->compare_at_price_by_percent = $variant['compare_at_price_by_percent'];
                  $variantPriceMarkup->compare_at_price_markup = $variant['compare_at_price_markup'];
                  $variantPriceMarkup->variant_id = $productVariant->id;
                  $variantPriceMarkup->save();
                  $productVariant->cost = $productVariant->calculateCost(
                      $variantPriceMarkup->price_markup,
                      $variantPriceMarkup->price_by_amount,
                      $variantPriceMarkup->price_by_percent,
                  );
                } else {
                    $productVariant->cost = $productVariant->calculateCost(
                        $productVariant->product->productPriceMarkup->price_markup,
                        $productVariant->product->productPriceMarkup->price_by_amount,
                        $productVariant->product->productPriceMarkup->price_by_percent,
                    );
                }
                $productVariant->save();

            } else {
                Yii::error([$productVariant->attributes, $productVariant->getErrors()], 'VariantSaveError');
                return $productVariant->getErrors();
            }
        }

    }


    public function updateUserSetting(User $user)
    {
        /* @var $setting UserSetting */
        $setting = $user->userSetting;

        if ($setting) {
            $setting->price_markup = $this->price_markup;
            $setting->compare_at_price_markup = $this->compareAtPrice_markup;
            $setting->price_by_percent = $this->priceByPercent;
            $setting->price_by_amount = $this->priceByAmount;
            $setting->compare_at_price_by_amount = $this->compareAtPriceByAmount;
            $setting->compare_at_price_by_percent = $this->compareAtPriceByPercent;
            $setting->save();

            return $setting;
        }
        return false;

    }

    public function createProductReviews(Product $product)
    {
        foreach ($this->reviews as $review) {
            if (empty($review['feedback']) || empty($review['name'])) {
                continue;
            }
            if (!$product->monitoring_reviews || ($product->monitoring_reviews && $review['star'] > $product->monitoring_reviews_min_rate)) {
                try {
                    $hashedReview = md5($review['feedback'] . $review['name'] . strtotime($review['date']));
                    $productReview = $product->getProductReviews()->where(['like', 'review_hash', $hashedReview])->one();
                    if (!$productReview) {
                        $productReview = new ProductReview();
                        $productReview->product_id = $product->id;
                        $productReview->reviewer_name = $review['name'];
                        $productReview->rate = $review['star'];
                        $productReview->review = $review['feedback'] ?? '';
                        $productReview->date = strtotime($review['date']);
                        $productReview->user_id = $product->user_id;
                        $productReview->review_hash = $hashedReview;
                        $productReview->status = ProductReview::STATUS_PUBLISHED;
                        if ($productReview->save()) {
                            if (!empty($review['reviewImages'])) {
                                foreach ($review['reviewImages'] as $image) {
                                    $reviewImage = new ProductReviewImage();
                                    $reviewImage->product_review_id = $productReview->id;
                                    $reviewImage->image_url = $image;
                                    $reviewImage->save();
                                }
                            }
                        } else {
                            Yii::error([$productReview->attributes, $productReview->getErrors()], 'CreateReviewValidationError');
                            echo "<pre>";
                            print_r($productReview->getErrors());die;
                        }
                    }
                } catch (\Exception $e) {
                    Yii::error([$e->getMessage(), 'file' => $e->getFile(), 'line'=> $e->getLine()], 'CreateReviewException');
                    echo "<pre>";
                    print_r($e->getMessage());die;
                    continue;
                }
            }
        }
    }

    public function updateShopifyProduct()
    {
        try {
            $variables = [
                "input" => array_merge([
                    "id" => $this->product->shopify_id
                ], $this->getProductData())
            ];
            $this->response = (new ShopifyGraphQLService($this->client))->updateProduct($variables);
            return $this->response;
        } catch (NotFoundException $exception) {
            Yii::error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString(), 'UpdateShopifyProduct');
            $this->product->is_published = 0;
            $this->product->shopify_id = null;
            $this->product->handle = null;
            $this->product->save();
        } catch (ClientException $exception) {
            Yii::error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString(), 'UpdateShopifyProduct');
        }
        return null;


    }

    public function updateShopifyVariantsPrices()
    {

    }

    public function updateShopifyVariantsInventory(array $updatedVariants)
    {
        $firstVariant = $this->product->productVariants[0];
        $inventoryLevel = (new ShopifyGraphQLService($this->client))->getInventoryItem($firstVariant->inventory_item_id) ?? null;
        if (!$inventoryLevel) {
            Yii::error([$this->product->id], 'DeletedVariantFromShopify');
            return false;
            usleep(100);
            $this->response = (new ShopifyGraphQLService($this->client))->getProduct($this->product->shopify_id);
            $this->setVariantIds();
            $inventoryLevel = (new ShopifyGraphQLService($this->client))->getInventoryItem($firstVariant->inventory_item_id) ?? null;
            if (!$inventoryLevel) {
                Yii::error($this->product->id, 'InventoryLevelNotFound');
                return false;
            }
        }

        foreach ($this->product->productVariants as $dbVariant) {
            if (isset($updatedVariants[$dbVariant->shopify_variant_id])) {
                $updateData = [
                    'inventory_item_id' => $dbVariant->inventory_item_id,
                    'location_id' => $inventoryLevel->getLocationId(),
                    'available' => $dbVariant->inventory_quantity
                ];
                try {
                    $this->client->getInventoryLevelManager()->set($updateData);
                    usleep(100);
                } catch (\Exception $exception) {

                    if (strpos($exception->getMessage(), 'Inventory item does not have inventory tracking enabled')) {
                        $this->product->monitoring_stock = 0;
                        $this->product->save();
                    } else {
                        Yii::error([
                            'error' => $exception->getMessage(),
                            'id' => $this->product->id,
                            'data' => $updateData
                        ], 'UpdateShopifyVariantsInventoryDefault');
                    }

                }
            }
        }
    }

    public function getSiteId($src_url): ?int
    {
        $productUrl = new ProductUrl(['url' => $src_url]);
        $site = $productUrl->getSite();
        return $site->id ?? null;

    }

	public function createProduct($user)
	{
		if ($this->new_collection_name && !$this->collection) {
            $response = (new ShopifyGraphQLService($this->client))->createCollection($this->new_collection_name);
			$this->collection = $response['id'];
		}
		$product = $this->saveProduct($user);
		if ($product) {
			if ($this->publish) {
				$product->addToPublishQueue();
				if (!$this->isFromQueue) {
                    Yii::$app->session->setFlash('publishQueueSet', 'Your product is published!');
                }
			} else {
			    if (!$this->isFromQueue) {
                    Yii::$app->session->setFlash('create', 'Your product is imported!');
                }
			}
			$this->updateUserSetting($user);

			return [
				'status' => 1,
				'id' => $product->id,
				'view' => $product->getPreviewUrl(),
				'shopifyView' => $product->getHandleUrl(),
				//'edit' => $product->getShopifyEditUrl(),
			];
		}
		return ['status' => 0, 'errors' => $this->getErrors()];
	}

	public function processBodyHtmlForQueue($bodyHtml)
    {
        $result = '<ul>';
        foreach ($bodyHtml as $bodyItem) {
            $result .= '<li>' . $bodyItem['attr_name'] . ': ' . $bodyItem['attr_value'] . '</li>';
        }

        return $result;
    }
}