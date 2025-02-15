<?php

namespace console\controllers;

use backend\helpers\CurrencyHelper;
use backend\models\Admin;
use backend\models\CaptchaSolverLog;
use common\clients\ShopifyGraphQlClient;
use Exception;
use common\models\{AlertCaptcha,
    AvailableSite,
    ImportQueue,
    MonitoringQueue,
    Product,
    ProductPublishQueue,
    ProductVariant,
    User
};
use frontend\models\api\ProductData;
use Slince\Shopify\Exception\ClientException;
use Throwable;
use Yii;
use yii\console\Controller;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class ProductController extends Controller
{
    public function actionMonitor(int $priority = 0)
    {
        set_time_limit(0);
        $priorityPlansIds = [4, 6];
        $query = Product::getMonitoringProductsQuery();
        if ($priority) {
            $query->andWhere([User::tableName() . '.plan_id' => $priorityPlansIds]);
        } else {
            $query->andWhere(['NOT IN', User::tableName() . '.plan_id', $priorityPlansIds]);
        }

        $query->limit(100);
        $productIds = $query->select(Product::tableName() . '.id')->column();
        if (!$productIds) {
            return true;
        }
        Product::updateAll(['monitored_at' => time()], ['id' => $productIds]);
        foreach ($productIds as $userId => $productId) {

            Yii::$app->params['user_id'] = $userId;
            $this->actionProductMonitor($productId);

        }
    }

    public function actionMonitorStore($id)
    {
        $query = Product::getMonitoringProductsQuery();
        $productIdsData = $query->select(Product::tableName() . '.id')->andWhere(['user_id' => $id])->batch(100);

        foreach ($productIdsData as $productIds) {
            foreach ($productIds as $productId) {
                $this->actionProductMonitor($productId['id']);
            }
        }
    }

    public function actionSheinOut()
    {
        $query = Product::find()
            ->joinWith('productVariants')
            ->andWhere(['monitoring_stock' => Product::MONITORING_ENABLE])
            ->andWhere(['site_id' => 1, 'is_deleted' => 0, 'is_published' => 1])
            ->andWhere(['>', Product::tableName() . '.created_at', strtotime('-5 months')])
            ->groupBy(ProductVariant::tableName() . '.product_id')
            ->having(new Expression('SUM(product_variants.inventory_quantity) = 0'));

        $products = $query->all();

        foreach ($products as $product) {
            /* @var Product $product */
            print_r([$product->id, $product->user_id, $product->src_product_url]);

            $productVariants = [];
            foreach ($product->productVariants as $productVariant) {
                $productVariant->inventory_quantity = 10;
                $productVariant->save();
                $productVariants[$productVariant->shopify_variant_id] = $productVariant;
            }
            $productDataModel = $product->getProductDataModel();
            try {
                $productDataModel->updateShopifyVariantsInventory($productVariants);

            } catch (\Throwable $exception) {
                echo $exception->getMessage();
            }
        }
    }

    public function actionProductMonitor($productId)
    {
        $product = Product::findOne($productId);

        if ($product->site->name == AvailableSite::SITE_TEMU) {
            if (!$product->getProductVariants()->andWhere(['>', 'inventory_quantity', 0])->exists()) {
                $product->monitored_at = time();
                $product->save();
                echo "Skip", PHP_EOL;
                return false;
            }
        }

        if ($product->site_id == 1 && !Admin::getOnlineCaptchaSolversCount()) {
            $product->monitored_at = strtotime('-1 day');
            $product->save();
            return false;
        }

        $queue = new MonitoringQueue();
        $queue->product_id = $productId;
        $queue->import_reviews = $product->user->userSetting->import_reviews;
        $queue->status = MonitoringQueue::STATUS_PENDING;
        $queue->save();
        echo "start {$productId} \n";
        $result = $queue->monitorQueues();
        $queue->status = $result ? MonitoringQueue::STATUS_SUCCESS : MonitoringQueue::STATUS_ERROR;
        $queue->save();
        echo "finished \n";
    }

    public function actionUpdate(int $id)
    {
        $product = Product::findOne($id);
        $user = $product->user;
        $model = $user->getProductDataModel();
        $productData = json_decode($product->product_data, true);
        $productData['variants'] = $product->getFilteredVariantsData();
        $model->setAttributes($productData);
        $model->setProduct($product);


        $model->updateShopifyVariantsInventory();
    }

    public function actionPublish(int $id)
    {
        $productPublish = ProductPublishQueue::findOne($id);
        if (!$productPublish) {
            return false;
        }
        if ($productPublish->status == ProductPublishQueue::STATUS_PUBLISHED) {
            return true;
        }
        $product = $productPublish->product;
        $user = $product->user;
        $model = $user->getProductDataModel();

        if ($productPublish->product->user->hasCachedDailyLimit()) {
            echo 'Daily variant creation limit reached. Please try again later!', PHP_EOL;
            $model->createNotification($product, 'Daily variant creation limit reached. Please try again later!');
            return false;
        }

        try {
            $product = $productPublish->product;
            if (!$product) {
                $productPublish->status = ProductPublishQueue::STATUS_ERROR;
                $productPublish->response_text = "Product doesn't exist";
                $productPublish->save();
                return false;
            }
            $productData = json_decode($product->product_data, true);

            if (
                $productData &&
                !empty($productData['product_type']) &&
                is_array($productData['product_type']) &&
                !empty($productData['product_type']['productTypeName']) &&
                strpos($product->src_product_url, AvailableSite::SITE_TOMTOP)
            ) {
                $productData['product_type'] = $productData['product_type']['productTypeName'] ?? '';
            }

            $productData['variants'] = $product->getFilteredVariantsData();
            $model->setAttributes($productData);
            $model->setProduct($product);
            $model->createShopifyProduct();

        } catch (ClientException $exception) {
            $response_message = $exception->getMessage();
            $errorData = json_decode($response_message, true);
            $error = array_values($errorData['errors'])[0][0];
        } catch (Exception $exception) {
            $response_message = $exception->getMessage();
            $error = $response_message;
        }
        $productPublish->status = ProductPublishQueue::STATUS_PUBLISHED;

        if (!empty($error)) {
            Yii::error([
                'product_id' => $productPublish->product->id,
                'url' => $productPublish->product->src_product_url,
                'user_id' => $productPublish->product->user_id,
                'plan' => $productPublish->product->user->plan->name,
                'error' => $response_message,
            ], 'PublishProductQueueClient');

            if (strpos($response_message, 'Daily variant creation') && !$productPublish->product->user->hasCachedDailyLimit()) {
                $productPublish->product->user->setCachedDailyLimit();
            }
            $model->createNotification($product, $error);
            $productPublish->status = ProductPublishQueue::STATUS_ERROR;
        }
        $productPublish->response_text = $response_message ?? null;
        $productPublish->save();
    }


    public function actionRunPendingQueues()
    {
        $queues = ProductPublishQueue::find()
            ->select('id')
            ->where(['>', 'created_at', strtotime('2025-01-01')])->andWhere(['status' => ProductPublishQueue::STATUS_PENDING])->column();


        foreach ($queues as $queueId) {
            $this->actionPublish($queueId);
        }


    }

    public function actionUpdateLocations($userId, $locationId)
    {
        $user = User::findOne($userId);
        if ($user && $locationId) {
            $productData = $user->getProductDataModel();
            $products = $user->getProducts()->where(['is_published' => 1])->all();
            foreach ($products as $product) {
                echo $product->id, PHP_EOL;
                $productData->setProduct($product);
                $productData->updateLocations($locationId);
            }
        }
    }

    public function actionCheckImportQueues()
    {
        $pendingQueues = ImportQueue::find()
            ->where([
                'status' => [ImportQueue::STATUS_PENDING]
            ])->andWhere(['<', 'created_at', strtotime('-4 minutes')])->all();

        foreach ($pendingQueues as $pendingQueue) {
            /* @var ImportQueue $pendingQueue */
            Yii::error($pendingQueue->getLogData(), 'ImportQueueIsPendingFiveMinutes');
            /* @var ImportQueue $pendingQueue */
            $pendingQueue->status = ImportQueue::STATUS_ERROR;
            $pendingQueue->save();
        }

        $processingQueues = ImportQueue::find()->where(['status' => ImportQueue::STATUS_PROCESSING])
            ->andWhere(['<', 'updated_at', strtotime('-5 minutes')])->all();

        if ($processingQueues) {
            foreach ($processingQueues as $processingQueue) {
                /* @var ImportQueue $processingQueue */
                Yii::error($processingQueue->getLogData(), 'CaptchaWasNotSolvedDuringTwoMinutes');
                $processingQueue->fail_count++;
                //$processingQueue->status = $processingQueue->fail_count > ImportQueue::MAX_FAIL_COUNT ? ImportQueue::STATUS_ERROR : ImportQueue::STATUS_PENDING;
                $processingQueue->status = ImportQueue::STATUS_ERROR;
                /*if ($processingQueue->status === ImportQueue::STATUS_ERROR) {
                    foreach ($processingQueue->alertCaptchas as $alertCaptcha) {
                        $alertCaptcha->status = AlertCaptcha::STATUS_EXPIRED;
                        $alertCaptcha->save();
                    }
                }*/
                $processingQueue->save();
            }
        }

        $pendingCaptchas = AlertCaptcha::find()
            ->where(['status' => AlertCaptcha::STATUS_PENDING])
            ->andWhere(['<', 'created_at', strtotime('10 minutes')])
            ->all();
        $totalPending = count($pendingCaptchas);
        if ($totalPending) {
            Yii::error($totalPending, 'PendingCaptchasTotal');
            foreach ($pendingCaptchas as $pendingCaptcha) {
                $pendingCaptcha->status = AlertCaptcha::STATUS_EXPIRED;
                $pendingCaptcha->save();
            }
        }

        $totalCaptchas = AlertCaptcha::find()
            ->where(['>=', 'created_at', strtotime('-30 minutes')])
            ->count();

        $unsolvedCaptchas = AlertCaptcha::find()
            ->where(['admin_id' => null])
            ->andWhere(['>=', 'created_at', strtotime('-30 minutes')])
            ->count();

        if ($unsolvedCaptchas === $totalCaptchas && $unsolvedCaptchas > 2) {
            $solvers = Admin::find()
                ->where(['role' => Admin::ROLE_CAPTCHA_SOLVER, 'is_online' => true])
                ->andWhere(['<', 'updated_at', strtotime('-30 minutes')])
                ->all();

            foreach ($solvers as $solver) {
                /* @var Admin $solver */
                $solver->is_online = false;
                if ($solver->save()) {
                    $log = CaptchaSolverLog::find()
                        ->where(['admin_id' => $solver->id])
                        ->orderBy(['activated_at' => SORT_DESC])
                        ->one();

                    if ($log) {
                        if ($log->activated_at == $log->deactivated_at) {
                            $log->deactivated_at = time();
                            $log->save();
                        }
                    } else {
                        $log = new CaptchaSolverLog();
                        $log->admin_id = $solver->id;
                        $log->deactivated_at = time();
                        $log->save();
                    }
                }
            }
        }
        
    }

    public function actionDeleteAllProducts(int $id)
    {
        $user = User::findOne($id);
        if (!$user) {
            return false;
        }
        $client = $user->graphQlClient;
        $products = $user->getProducts()->select(['shopify_id', 'id'])->indexBy('id')->column();
        print_r($products);
        $shopifyIds = array_unique($products);
        Product::deleteAll(['id' => array_keys($products)]);
        foreach (array_values($shopifyIds) as $key => $shopifyId) {
            echo $key + 1, '.', $shopifyId, PHP_EOL;
            try {
                $client->deleteProduct($shopifyId);
                usleep(1000);
            } catch (\Throwable $exception) {
                echo $exception->getMessage(), PHP_EOL;
                sleep(1);
            }

        }
    }

    public function actionPublishUnpublishedProducts()
    {
        $user = User::find()->where(['id' => 23261])->one();

        if (!$user) {
            echo 'User not found';
            return;
        }

        $site = AvailableSite::find()->where(['name' => AvailableSite::SITE_TOMTOP])->one();

        if (!$site) {
            echo 'Site not found';
            return;
        }

        /* @var User $user */
        $productIds = $user->getProducts()
            ->andWhere([Product::tableName() . '.site_id' => $site->id])
            ->andWhere([Product::tableName() . '.is_published' => Product::PRODUCT_IS_NOT_PUBLISHED])
            ->innerJoinWith('publishQueues')
            ->distinct()
            ->select([Product::tableName() . '.id'])
            ->column();

        foreach ($productIds as $key => $id) {
            echo $key, '. - ', $id, ' - ';
            $publishQueue = new ProductPublishQueue();
            $publishQueue->product_id = $id;
            $publishQueue->status = ProductPublishQueue::STATUS_PENDING;

            if ($publishQueue->save()) {
                $this->actionPublish($publishQueue->id);
                echo 'published', PHP_EOL;
            }
        }
    }

    public function actionFixShopifyVariants()
    {
        $products = Product::find()
            ->innerJoinWith('productVariants')
            ->where(['products.is_published' => 1])
            ->andWhere(['>=', 'products.created_at', time() - (60 * 60 * 24 * 12)])
            ->andWhere(['product_variants.shopify_variant_id' => null])
            ->groupBy('products.id')  // Группируем по продуктам
            ->having(['COUNT(product_variants.id)' => 1])  // Ограничиваем продуктами с ровно 1 вариантом
            ->all();

        foreach ($products as $product) {
            /** @var Product $product */
            echo $product->id . PHP_EOL;
            $client = new ShopifyGraphQlClient($product->user);
            try {
                $shopifyProductData = $client->getProduct($product->shopify_id);
            } catch (Throwable $e) {
                Yii::error('FixShopifyVariantsError ' . $e->getMessage() . ' productId ' . $product->id);
                continue;
            }

            if (empty($shopifyProductData)) {
                $product->is_published = false;
                $product->save();
                continue;
            }

            if (!empty($shopifyProductData['variants'][0]['sku'])) {
                print_r($shopifyProductData);
                continue;
            }

            $optionsData = [
                'productId' => Product::SHOPIFY_ID_PREFIX . $product->shopify_id,
                'options' => ['name' => 'Title', 'values' => ['name' => '1'], 'position' => 1]
            ];

            $client->createProductOptions($optionsData);
            /** @var ProductData $productData */
            $productData = $product->getProductDataModel();
            $shopifyProductData = $client->getProduct($product->shopify_id);
            $variant = $productData->getGraphQlVariants($product->productVariants, $product->shopify_id);
            $dbVariant = $product->productVariants[0];
            $shopifyVariants = $client->createProductVariants($variant);
            $client->deleteVariant(['productId' => Product::SHOPIFY_ID_PREFIX . $product->shopify_id, 'variantsIds' => ['gid://shopify/ProductVariant/' . $shopifyProductData['variants'][0]['id']]]);

            if (count($shopifyVariants)) {
                $dbVariant->shopify_variant_id = $shopifyVariants[0]['id'];
                $dbVariant->save();
                $productData->setVariantPrices();
            } else {
                Yii::error('FixShopifyVariants VariantsCreateError productId' . $product->id);
            }


        }
    }
}
