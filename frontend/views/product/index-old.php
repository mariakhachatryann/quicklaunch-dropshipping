<?php
use yii\grid\GridView;
use yii\helpers\Html;
use common\models\Product;
use yii\helpers\StringHelper;
use yii\helpers\Url;

$this->title = 'Products';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="box box-primary box box-primary box-padding">
    <?= Html::a('Import product', ['/product/import-product'], ['class'=>'btn btn-primary pull-right', 'target' => '_blank'])?>
    <h1><?= Html::encode($this->title) ?></h1>

    <div style="overflow-x:auto;width:100%">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout'=>"{items}{pager}",
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'id',
                    'contentOptions' => ['style' => 'width:100px; text-align: center']
                ],
                [
                    'attribute' => 'product_data',
                    'label' => 'Image',
                    'format' => 'raw',
                    'contentOptions' => ['style' => 'width:50px; text-align: center'],
                    'value' => function ($model) {
                        $data = json_decode($model->product_data, true);
                        $image = $data['images'][0];
                        return Html::img($image,['style' => 'width:50px']);
                    },
                ],
                [
                    'attribute' => 'title',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::a(StringHelper::truncateWords($model->title,10),
                            $model->src_product_url, ['target'=>'_blank']);
                    },
                ],
                [
                    'attribute' => 'sku',
                    'contentOptions' => ['style' => 'width:150px; text-align: center']
                ],
                [
                    'attribute' => 'shopify_id',
                    'contentOptions' => ['style' => 'width:150px; text-align: center']
                ],
                [
                    'attribute' => 'is_deleted',
                    'filter' => [Product::PRODUCT_IS_NOT_DELETED => 'Active', Product::PRODUCT_IS_DELETED => 'Deleted'],
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'product-status'],
                    'label' => 'Status',
                    'value' => function ($model) {
                        return $model->is_deleted ?
                            "<span class='label label-danger'>Deleted</span>" :
                            "<span class='label label-success'>Active</span>";
                    }
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => 'Actions',
                    'headerOptions' => ['style' => 'color:#337ab7'],
                    'template' => '{view}{update}{reviews}{delete}',
                    'buttons' => [
                        'view' => function ($url, $model) {
                            if ($model->is_deleted) {
                                return '';
                            }

                            return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', Yii::$app->user->identity->shopUrl."/products/$model->handle", [
                                'title' => 'View Product on Shopify',
                                'target' => '_blank'
                            ]);
                        },
                        'update' => function ($url, $model) {
                            if ($model->is_deleted) {
                                return '';
                            }
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>', Yii::$app->user->identity->shopUrl."/admin/products/$model->shopify_id", [
                                'title' => 'Update Product on Shopify',
                                'target' => '_blank'
                            ]);
                        },
                        'reviews' => function ($url, $model) {
                            if ($model->is_deleted) {
                                return '';
                            }
                            return Html::a('<span class="glyphicon glyphicon-thumbs-up"></span>', Url::to(['/product-review','ProductReviewSearch[product_id]' => $model->shopify_id]), [
                                'title' => 'View Product Reviews',
                                'target' => '_blank'
                            ]);
                        },
                        'delete' => function ($url, $model) {
                            if ($model->is_deleted) {
                                return '';
                            }
                            return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['product/delete', 'id'=>$model->id], [
                                'title' => 'Delete Product',
                                'data-confirm' => 'Are You sure You want to delete this product from Shopify'
                            ]);
                        }
                    ],
                ],

            ],
        ]) ?>
    </div>

</div>



