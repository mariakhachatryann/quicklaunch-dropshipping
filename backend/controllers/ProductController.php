<?php

namespace backend\controllers;

use backend\models\Admin;
use common\helpers\DhgateHelper;
use common\models\ImportQueue;
use Orhanerday\OpenAi\OpenAi;
use Yii;
use common\models\Product;
use backend\models\ProductSearch;
use yii\data\{ActiveDataProvider, ArrayDataProvider};
use yii\web\NotFoundHttpException;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends AdminController
{
	protected $allowedRoles = [Admin::ROLE_SUPPORT];

    public function actionDhgate()
    {
        echo '<pre>';
        $dhgate = new DhgateHelper();
        $url = 'https://www.dhgate.com/product/leisure-gentleman-flats-shoes-red-bottom/724783615.html';
        $d = $dhgate->getProduct('', $url);
        print_r($d);die;
    }

    public function actionScrap()
    {
        return Yii::$app->request->userIP;
    }
    
    public function actionVko()
    {        $open_ai = new OpenAi(Yii::$app->params['openAiKey']);


        $complete = $open_ai->chat([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Generate title for this product https://us.shein.com/SHEIN-SXY-Tie-Dye-Fitted-Tube-Dress-p-10654284-cat-1727.html'
                ]
            ]
        ]);
        
        
        echo '<pre>';
        print_r(json_decode($complete, true));
        die;
        
        $chat = $open_ai->completion([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    "role" => "user",
                    "content" => "How are you?"
                ],
               
            ],
        ]);
    
    
        print_r($chat);
        
        
    }
    
    /**
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Product model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $variantQuery = $model->getProductVariants();
        $variantChangesQuery = $model->getProductVariantChanges();
        $variantsDataProvider = new ActiveDataProvider(['query' => $variantQuery, 'pagination' => false]);
        $variantChangesDataProvider = new ActiveDataProvider(['query' => $variantChangesQuery]);
        $variantsShopifyDataProvider = new ArrayDataProvider([
            'pagination' => false,
            'allModels' => $model->shopify_id ? $model->user->getShopifyApi()->getProductVariantManager()->findAll($model->shopify_id) : []
        ]);
        return $this->render('view', compact('model', 'variantsDataProvider', 'variantChangesDataProvider', 'variantsShopifyDataProvider'));
    }

    /**
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Product();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Product model.
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
     * Deletes an existing Product model.
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
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
