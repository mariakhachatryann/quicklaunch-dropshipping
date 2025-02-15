<?php

namespace frontend\controllers;

use common\models\Category;
use frontend\models\NicheSelectionForm;
use frontend\models\UserNiche;
use Yii;
use common\models\Niche;

class StoreController extends UserController
{
    public function actionNicheSelection()
    {
        $model = new NicheSelectionForm();

        $niches = Niche::find()->orderBy(['is_trending' => SORT_DESC])->all();

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post()) && $model->validate()) {
            $selectedNicheId = $model->niche_id;

            UserNiche::deleteAll(['user_id' => Yii::$app->user->id]);

            $userNiche = new UserNiche();
            $userNiche->user_id = Yii::$app->user->id;
            $userNiche->niche_id = $selectedNicheId;
            $userNiche->save();

            return $this->redirect(['category-setup']);
        }

        return $this->render('niche-selection', [
            'niches' => $niches,
            'model' => $model,
        ]);
    }
    public function actionCategorySetup()
    {
        $userNiche = UserNiche::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->one();

        if (!$userNiche) {
            Yii::$app->session->setFlash('error', 'Please select a niche first.');
            return $this->redirect(['niche-selection']);
        }

        $categories = Category::find()->where(['niche_id' => $userNiche->niche_id])->all();

        if (Yii::$app->request->isPost) {
            $selectedCategoryIds = array_column(Yii::$app->request->post('categories'), 'id');
            $newCategory = Yii::$app->request->post('new_category');
            if (isset($newCategory)) {
                $category = new Category();
                $category->niche_id = $userNiche->niche_id;
                $category->name = $newCategory;
                $category->save();
            }

            $selectedCategoryIds[] = $category->id;
            if (empty($selectedCategoryIds)) {
                Yii::$app->session->setFlash('error', 'Please select at least one category.');
                return $this->refresh();
            }

            $this->syncCategoriesWithShopify($selectedCategoryIds);

            Yii::$app->session->setFlash('success', 'Selected categories synced with Shopify.');
            return $this->refresh();
        }

        return $this->render('category-setup', [
            'categories' => $categories,
            'niche' => $userNiche->niche->name
        ]);
    }


    public function syncCategoriesWithShopify($categoryIds)
    {
        $shopifyApi = Yii::$app->user->identity->getShopifyApi();
        $categoriesToSync = Category::find()->where(['id' => $categoryIds])->all();
        if (empty($categoriesToSync)) {
            Yii::$app->session->setFlash('error', 'No valid categories found to sync.');
            return;
        }

        $userNiche = UserNiche::find()->where(['user_id' => Yii::$app->user->id])->one();
        $nicheName = $userNiche ? $userNiche->niche->name : 'Default Niche';

        foreach ($categoriesToSync as $categoryData) {
            $mutation = '
                mutation {
                    collectionCreate(input: {
                        title: "' . $categoryData->name . '"
                    }) {
                        collection {
                            id
                            title
                        }
                        userErrors {
                            field
                            message
                        }
                    }
                }';

            $response = $shopifyApi->query($mutation);

            if ($response->getStatusCode() == 200) {
                Yii::$app->session->setFlash('success', 'Categories synced with Shopify collection: ' . $nicheName);
            } else {
                Yii::$app->session->setFlash('error', 'Failed to add category to collection: ' . $categoryData->name . '. Error: ' . $response['errors'][0]['message']);
            }
        }
    }
}