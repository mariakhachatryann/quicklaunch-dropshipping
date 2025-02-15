<?php

namespace frontend\controllers;

use backend\models\ProductSearch;

use common\helpers\HelpTextHelper;
use console\jobs\ProductDeleteJob;
use frontend\models\ErrorMessageRequest;
use frontend\models\ProductVariantSearch;
use common\models\{AvailableSite,
    Currency,
    Feature,
    ImportQueue,
    Product,
    ProductPriceMarkup,
    ProductUrl,
    RequestedSite,
    User,
    VariantChange,
    Video
};
use frontend\models\RecommendedProduct;
use frontend\models\UserNiche;
use Prophecy\Argument\Token\AnyValuesToken;
use Yii;
use yii\web\{Cookie, ForbiddenHttpException, NotFoundHttpException, Request, Response};
use yii\base\BaseObject;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\VerbFilter;
use yii\helpers\Url;

class ProductController extends UserController
{
    const COOKIE_DISPLAY_TYPE = 'display_type';


    /**
     * {@inheritDoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'publish' => ['POST'],
            ],
        ];
        return $behaviors;
    }

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $user = Yii::$app->user->identity;
            if (is_null($user->plan_id)) {
                $this->owner->redirect(['profile/subscribe'])->send();
                return false;
            }
            return true;
        }
        return false;
    }

    public function actionIndex()
    {
        $cookiesValue = Yii::$app->request->cookies->get(self::COOKIE_DISPLAY_TYPE);
        $displayType = $cookiesValue->value ?? Product::DISPLAY_TABLE_STYLE;
        $searchModel = new ProductSearch();
        $searchModel->is_deleted = 0;
        $searchModel->user_id = Yii::$app->user->identity->id;
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);
        $planFeatures = Feature::getPlanSettingkeys(Yii::$app->user->identity);
        $allowDeleteMultipleProducts = isset($planFeatures['delete_multiple_products']);
        $allowBulkMonitoring = isset($planFeatures['products_bulk_monitoring']);
        $allowMonitorNow = isset($planFeatures['monitor_now']);
        return $this->render('index', compact('dataProvider', 'searchModel', 'displayType', 'allowDeleteMultipleProducts', 'allowBulkMonitoring', 'allowMonitorNow'));
    }

    public function actionView($id)
    {
        $product = $this->findModel($id);

        if ($product) {
            $updateProduct = false;
            if (!$product->productPriceMarkup) {
                $product->setPriceMarkup();
                unset($product->productPriceMarkup);
                $product->save();
                $updateProduct = true;
            }
            if (is_null($product->currency_rate)) {
                $defaultCurrency = Currency::getDb()->cache(function ($db) {
                    return Currency::findOne(['code' => Currency::DEFAULT_CURRENCY]);
                }, 1000);
                $product->currency_rate = 1;
                $product->currency_id = $defaultCurrency->id;
                $product->default_currency_id = $defaultCurrency->id;
                $product->save();
                $updateProduct = true;
            }

            $product = $updateProduct ? $this->findModel($id) : $product;

            $variantSearch = new ProductVariantSearch();
            $variantSearch->product_id = $id;
            $variantChangesQuery = $product->getProductVariantChanges()->orderBy('id DESC');
            $currencies = Currency::getDb()->cache(function ($db) {
                return Currency::find()->all();
            }, 1000);
            $planFeatures = Feature::getPlanSettingkeys($product->user);
            $variantsDataProvider = $variantSearch->search(Yii::$app->request->queryParams);
            $variantsDataProvider->pagination = false;
            $variantChangesDataProvider = new ActiveDataProvider(['query' => $variantChangesQuery, 'pagination' => array('pageSize' => 50)]);
            $userLimits = $product->user->limits;
            $remainingMonitorings = $userLimits['monitoringRemainsLimit'] ?? 0;
            return $this->render('view', ['product' => $product,
                'variantChangesDataProvider' => $variantChangesDataProvider,
                'variantsDataProvider' => $variantsDataProvider,
                'variantSearch' => $variantSearch,
                'currencies' => $currencies,
                'allow_change_currency' => isset($planFeatures['product_currency_convertor']),
                'allow_variant_price_markup' => isset($planFeatures['variant_price_markup']),
                'allow_custom_pricing_rules' => isset($planFeatures['custom_pricing_rules']),
                'allow_review_monitoring' => isset($planFeatures['monitor_reviews']),
                'allow_monitor_now' => isset($planFeatures['monitor_now']),
                'reviewsRemainsLimit' => $userLimits['reviewsRemainsLimit'] ?? 0,
                'user_has_remaining_monitorings' => $remainingMonitorings > 0
            ]);
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionRestore(int $id)
    {
        $product = $this->findModel($id);
        $product->is_deleted = Product::PRODUCT_IS_NOT_DELETED;
        $product->save();
        Yii::$app->session->setFlash('success', 'The product has been restored');
        return $this->redirect(Yii::$app->request->referrer);
    }

    protected function findModel($id)
    {
        if (($model = Product::findOne(['id' => $id, 'user_id' => Yii::$app->user->identity->id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionDisplayType($type)
    {
        $cookies = Yii::$app->response->cookies;
        $cookies->add(new Cookie([
            'name' => self::COOKIE_DISPLAY_TYPE,
            'value' => $type,
        ]));
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionDelete($id)
    {
        $product = $this->findModel($id);
        $user = Yii::$app->user->identity;
        /* @var $user User */
        if ($product->is_published) {
            $product->is_published = 0;
            try {
                $query = <<<QUERY
                  mutation {
                    productDelete(input: {id: "gid://shopify/Product/{$product->shopify_id}"}) {
                      deletedProductId
                      userErrors {
                        field
                        message
                      }
                    }
                  }
                QUERY;

                $user->shopifyApi->query(["query" => $query]);
            } catch (\Exception $e) {

            }
        }
        $product->delete();
        /*$productVariantsIds = $product->getProductVariants()->select('id')->column();
        if ($productVariantsIds) {
            VariantChange::deleteAll(['variant_id' => $product->getProductVariants()->select('id')]);
        }*/
        Yii::$app->session->setFlash('danger', 'Your product has been removed from shopify!');
        return $this->redirect(['/products']);
    }

    public function actionCreate($id = null)
    {
        $productData = null;
        if ($id) {
            $product = $this->findModel($id);
            $productData = json_decode($product->product_data, true);
            $productData['variants'] = $product->getFilteredVariantsData();
            $productData['id'] = $id;
            $productData = json_encode($productData);
        }
        Yii::$app->view->params['switchMenu'] = false;

        $sites = AvailableSite::find()->indexBy('id')->all();
        $planSiteIds = Yii::$app->user->identity->plan->getPlanSites()->select('site_id')->column();
        $planFeatures = Feature::getPlanSettingkeys(Yii::$app->user->identity);
        $allowMultipleImport = isset($planFeatures['multiple_import']);
        $allowBulkImport = isset($planFeatures['bulk_import']);
        $trainingVideos = Video::find()->indexBy('id')->where(['id' => array_merge(Video::IMPORT_VIDEO_IDS, Video::IMPORT_SPECIFIC_VIDEO_IDS)])->all();

        $requestedSiteModel = new RequestedSite();

        return $this->render('create', compact('productData', 'trainingVideos', 'sites', 'planSiteIds', 'requestedSiteModel', 'allowMultipleImport', 'allowBulkImport'));
    }

    public function actionImportProductData()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        /* @var User $user */
        $user = Yii::$app->user->identity;

        $model = new ProductUrl();
        if ($model->load(Yii::$app->request->get(), '') && $model->validate()) {
			return $model->importProductData($user);
        }
        throw new NotFoundHttpException('Invalid product url!');
    }

    public function actionEditDescription()
    {
        $productId = Yii::$app->request->post('productId');
        $product = $this->findModel($productId);
        $data = json_decode($product->product_data, true);
        $data['body_html'] = Yii::$app->request->post('data');
        $product->product_data = json_encode($data);
        $product->save();

        if ($product->is_published) {
            $product->productDataModel->updateDescription();
        }

        return true;

    }

    public function actionImportProductReviews()
    {
        $reviewsData = '';
        $model = new ProductUrl();
        if ($model->load(Yii::$app->request->post(), '') && $model->validate()) {
            $reviewsData = $model->getProductReviewData();
        }
        return $reviewsData;
    }

    public function actionMonitoringReviewMinRate()
    {
        $productId = Yii::$app->request->post('productId');
        $rate = Yii::$app->request->post('rate');
        $product = $productId ? $this->findModel($productId) : null;

        if ($product) {
            $product->monitoring_reviews_min_rate = $rate;
            $product->save();
        }
    }

    public function actionMonitoring()
    {
        $user = Yii::$app->user->identity;
        /* @var $user User */
        $productId = Yii::$app->request->post('productId');
        $checked = Yii::$app->request->post('checked');
        $checked = $checked == 'true' ? true : false;
        $type = Yii::$app->request->post('type');
        $product = $productId ? $this->findModel($productId) : null;

        if ($product) {
            $userLimits = $user->limits;
            $remainingMonitorings = $userLimits['monitoringRemainsLimit'] ?? 0;
            if ($checked) {
                if ($remainingMonitorings < 1) {
                    return json_encode(['success' => 0, 'message' => 'Product monitoring limit is expired']);
                }
            }

            switch ($type){
                case 'stock':
                    $fieldName = 'monitoring_stock';
                    $haseActiveMonitoring = $product->monitoring_price || $product->monitoring_reviews;
                    break;
                case 'price';
                    $fieldName = 'monitoring_price';
                    $haseActiveMonitoring = $product->monitoring_stock || $product->monitoring_reviews;
                    break;
                case 'review';
                    $fieldName = 'monitoring_reviews';
                    $haseActiveMonitoring = $product->monitoring_price || $product->monitoring_stock;
                    break;
            };

            $remained = $remainingMonitorings;

            if (!$haseActiveMonitoring) {
                $remained = $checked ? $remainingMonitorings - 1 : $remainingMonitorings + 1;
            }

            $info = $checked ? 'activated' : 'deactivated';
            $product->{$fieldName} = $checked;
            $product->monitored_at = null;
            $product->save();

            return json_encode([
                'success' => 1,
                'message' => "You have $remained product monitoring limit",
                'info' => "You have $info product monitoring"
            ]);
        }
    }

    public function actionPublish($id)
    {
        $product = $this->findModel($id);
        $product->addToPublishQueue();
        Yii::$app->session->setFlash('publishQueueSet', 'Your request is added to queue. We will let you know when the product will be published in Shopify!');
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionPopUpErrors()
    {
        $model = new ErrorMessageRequest();
        if ($model->load(Yii::$app->request->post(), '') && $model->validate()) {
            Yii::info($model->attributes, 'PopUpJsError');
        }
    }

    public function actionUpdateProductCurrency($id)
    {
        $product = $this->findModel($id);
        $oldRate = $product->currency_rate;
        $product->scenario = Product::SCENARIO_LOAD_CURRENCY_RATE;
        $product->load(Yii::$app->request->post());
        $maxPrice = 0;

        if (!$product->validate()) {
            Yii::$app->response->statusCode = 400;
            return json_encode($product->getErrors());
        }

        $variants = $product->productVariants;

        if ($product->getOldAttribute('currency_rate') != $product->currency_rate) {
            foreach ($variants as $variant) {
                $data = $variant->updatePrice($maxPrice, $oldRate, $product->currency_rate);
                $variant->price = $data['price'];
                $variant->compare_at_price = $data['compare_at_price'];
                if (!$variant->save()) {
                    Yii::$app->response->statusCode = 400;
                    return json_encode($variant->getErrors());
                }
            }

            $data = json_decode($product->product_data);
            $data->price = $maxPrice;
            $product->product_data = json_encode($data);

            if ($product->is_published) {
                $product->productDataModel->setVariantPrices();
            }
        }

        if (!$product->save()) {
            return $product->getErrors();
        }

        return true;
    }

    /**
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionCreateRequestedSite()
    {
        $model = new RequestedSite();

        if (!Yii::$app->request->isPost) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $adminUrl = Url::base(true) . '/admin/requested-site/view?id=' . $model->id;
            Yii::$app->telegram->sendMessage(implode(PHP_EOL, [
                "New requested site",
                "User id: {$model->user->id}",
                "User Name: {$model->user->username}",
                "Url: {$model->url}",
                '', '',
                $adminUrl
            ]));

            $createdSuccessfully = HelpTextHelper::getHelpText('requested_site_created_successfully', 'text');
            Yii::$app->session->setFlash('success', $createdSuccessfully);
        } else {
            Yii::$app->session->setFlash('error', $model->getFirstError('url'));
        }

        return $this->redirect('create');
    }

    public function actionUpdateProductPriceMarkup($id)
    {
        $product = $this->findModel($id);

        if ($product) {
            $productPriceMarkup = $product->productPriceMarkup;
            $oldPriceMarkup = clone $productPriceMarkup;
            $productPriceMarkup->scenario = ProductPriceMarkup::SCENARIO_UPDATE_PRODUCT_MARKUP;
            $maxPrice = 0;

            if ($productPriceMarkup->load(Yii::$app->request->post()) && $productPriceMarkup->save()) {
                $product = $this->findModel($id);
                foreach ($product->productVariants as $variant) {
                    $data = $variant->updatePriceWithMarkup($maxPrice, $oldPriceMarkup);
                    $variant->price = $data['price'];
                    $variant->compare_at_price = $data['compare_at_price'];

                    if (!$variant->save()) {
                        return true;
                    }

                    $data = json_decode($product->product_data);
                    $data->price = $maxPrice;
                    $product->product_data = json_encode($data);
                    $product->save();
                }

                if ($product->is_published) {
                    $product->productDataModel->setVariantPrices();
                }

                return true;
            }
            return true;
        }

        return true;

    }

    public function actionAddToDeleteQueue()
    {
        $productIds = \Yii::$app->request->post('ids');
        /* @var User $user */
        $user = \Yii::$app->user->identity;
        $shopifyProductIds = $user->getProducts()
            ->where(["IN", "id", $productIds])
            ->andWhere(['is_published' => Product::PRODUCT_IS_PUBLISHED])
            ->select('shopify_id')->column();

        foreach ($shopifyProductIds as $shopifyProductId) {
            Yii::$app->queue->push(new ProductDeleteJob([
                'shopifyId' => $shopifyProductId,
                'userId' => Yii::$app->user->id
            ]));
        }

        $removeIds = $user->getProducts()
            ->where(['IN', 'id', $productIds])->select('id')->column();

        if ($removeIds) {
            Product::deleteAll(['IN', 'id', $removeIds]);
        }
        foreach ($shopifyProductIds as $shopifyProductId) {
            try {
                $user->getShopifyApi()->getProductManager()->remove($shopifyProductId);
            } catch (\Exception $exception) {
                //Yii::error($exception->getMessage(), 'UnableDeleteProduct');
            }
        }

        return true;
    }


}