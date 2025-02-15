<?php

namespace frontend\controllers;

use backend\models\ProductSearch;
use backend\models\RecommendedProductSearch;
use common\models\Category;
use common\models\Feature;
use common\models\Product;
use common\models\User;
use frontend\models\UserNiche;
use Yii;
use frontend\models\RecommendedProduct;
use yii\web\NotFoundHttpException;

/**
 * RecommendedProductController implements the CRUD actions for RecommendedProduct model.
 */
class RecommendedProductController extends UserController
{
    /**
     * Lists all RecommendedProduct models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new RecommendedProductSearch();
        $categoryId = Yii::$app->request->get('categoryId');

        $params = array_merge(
            Yii::$app->request->queryParams,
            ['user_id' => Yii::$app->user->id]
        );
        $user = Yii::$app->user->identity;
        /* @var User $user */
        $setting = $user->userSetting;

        if ($categoryId) {
            $params['categoryId'] = $categoryId;
        }

        $dataProvider = $searchModel->search($params);
        $collections = [];
//        $allCollections = $user->getShopifyApi()->getCustomCollectionManager()->findAll();
//        foreach ($allCollections as $collection) {
//            $collections[] = [
//                'id' => $collection->getId(),
//                'title' => $collection->getTitle()
//            ];
//        }

        return $this->render('index', compact('dataProvider', 'searchModel', 'setting', 'collections'));
	}

//    public function actionIndex()
//    {
//        $searchModel = new RecommendedProductSearch();
//        $searchModel->product_type_id = 'all';
//        $searchModel->has_original_data = true;
//        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
//
//        $user = Yii::$app->user->identity;
//        /* @var User $user */
//        $setting = $user->userSetting;
//
//        $collections = [];
//        $allCollections = $user->getShopifyApi()->getCustomCollectionManager()->findAll();
//        foreach ($allCollections as $collection) {
//            $collections[] = [
//                'id' => $collection->getId(),
//                'title' => $collection->getTitle()
//            ];
//        }
//
//        return $this->render('index', compact( 'dataProvider', 'searchModel', 'setting', 'collections'));
//    }

    /**
     * Displays a single RecommendedProduct model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new RecommendedProduct model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new RecommendedProduct();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing RecommendedProduct model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing RecommendedProduct model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the RecommendedProduct model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return RecommendedProduct the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = RecommendedProduct::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionFindProductByCategory()
    {
        $userNiche = UserNiche::findOne(['user_id' => Yii::$app->user->id]);
        if (!$userNiche) {
            Yii::$app->session->setFlash('error', 'Please select a niche first.');
            return $this->redirect(['niche-selection']);
        }

        $selectedCategory = Yii::$app->request->post('category');
        $category = Category::findOne(['name' => $selectedCategory, 'niche_id' => $userNiche->niche_id]);
        if (!$category) {
            Yii::$app->session->setFlash('error', 'The selected category does not exist in your niche.');
            return $this->redirect(['store/niche-selection']);
        }

        $searchModel = new RecommendedProductSearch([
            'category_id' => $category->id,
        ]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', compact('dataProvider', 'searchModel'));
    }

    private function modifyProductData($data, $markup)
    {
        $data = json_decode($data, true);

        $optionsIndex = 1;
        $responseVariants = [];
        $optionsKeys = [
            "IMG" => 'img',
            "SKU" => 'sku',
            "Price" => 'price',
            "CompareAtPrice" => 'compare_at_price',
            "Quantity" => 'inventory_quantity',
        ];

        if (empty($data['variants'])) {
            return null;
        }


        if (is_array($data['body_html'])) {
            $description = '';

            foreach ($data['body_html'] as $item) {
                $description .= "<p>" . $item['attr_name'] . ":" . $item['attr_value'] . "</p>";
            }

            $data['body_html'] = $description;
        }

        foreach ($data['variants'] as $key => $variant) {
            if ($key > 0) {
                $variantData = [];
                $itemIndex = 0;
                foreach ($variant as $itemKey => $itemValue) {
                    if (isset($data['onlyOptionName'][$itemIndex])) {
                        if (isset($optionsKeys[$data['onlyOptionName'][$itemIndex]])) {
                            $nameKey = $optionsKeys[$data['onlyOptionName'][$itemKey]];

                            if ($nameKey == 'price' || $nameKey == 'compare_at_price') {
                                $price = floatval($itemValue['name']);

                                if ($nameKey == 'price') {

                                    if ($markup['price_markup']) {
                                        $price+= $markup['price_by_amount'];
                                    } else {
                                        $price += (($price / 100) * $markup['price_by_percent']);
                                    }

                                } else {
                                    if ($markup['compare_at_price_markup']) {
                                        $price+= $markup['compare_at_price_by_amount'];
                                    } else {
                                        $price += (($price / 100) * $markup['compare_at_price_by_percent']);
                                    }
                                }
                                $variantData[$nameKey] = $price;
                            } else {
                                $variantData[$nameKey] = $itemValue['name'];
                            }

                        } else {
                            $variantData["option$optionsIndex"] = $itemValue['name'];
                            $optionsIndex++;
                        }
                    } else {
//                        $variantData['default_sku'] = $itemValue['name'];
                    }

                    $itemIndex++;
                }
                $responseVariants[] = $variantData;
            }
            $optionsIndex = 1;
        }

        if ($markup['price_markup']) {
            $data['price'] = floatval($data['price']) + $markup['price_by_amount'];
        } else {
            $data['price'] = floatval($data['price']) + ((floatval($data['price']) / 100) * $markup['price_by_percent']);
        }

        $data['variants'] = $responseVariants;
        $data['price_markup'] = $markup['price_markup'];
        $data['compareAtPrice_markup'] = $markup['compare_at_price_markup'];
        $data['priceByPercent'] = $markup['price_by_amount'];
        $data['priceByAmount'] = $markup['price_by_percent'];
        $data['compareAtPriceByAmount'] = $markup['compare_at_price_by_amount'];
        $data['compareAtPriceByPercent'] = $markup['compare_at_price_by_percent'];
        return $data;
    }


    public function actionBulkImport($publish = false)
    {
        $data = Yii::$app->request->post();
        $productIds = json_decode($data['bulkImportProductIds'], true);
        $markup = is_array($data['UserSetting']) ? $data['UserSetting'] : json_decode($data['UserSetting'], true);

        if (empty($productIds)) {
            return $this->redirect(['index']);
        }

        foreach ($productIds as $productId) {
            $recommendedProduct = RecommendedProduct::find()->where(['id' => $productId])->one();

            if (empty($recommendedProduct) || empty($recommendedProduct->product_data)) {
                continue;
            }

            $dataForImport = $this->modifyProductData($recommendedProduct->product_data, $markup);

            $user = Yii::$app->user->identity;
            /* @var $user User */
            $model = $user->getProductDataModel();

            if ($model->load($dataForImport, '') && $model->validate()) {
                $model->publish = $publish;
                $model->createProduct($user);
            }
        }

        Yii::$app->session->setFlash('success', 'Products Created!');
        return $this->redirect('/products');
    }
}
