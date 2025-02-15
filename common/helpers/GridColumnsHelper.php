<?php


namespace common\helpers;


use common\models\Product;
use common\models\ProductVariant;
use yii\grid\ActionColumn;
use yii\helpers\Html;

class GridColumnsHelper
{
    public static function getVariantChangeOptions(Product $product): array
    {
        $productData = $product->getProductData();
        $options = $productData['options'] ?? [];
        $columns = [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'img',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::img($model->img, ['style' => 'width:50px; text-align: center']);
                },
            ],
            'shopify_variant_id'
        ];
        foreach ($options as $key => $option) {
            $columns[] = [
                'attribute' => 'option' . ($key + 1),
                'label' => $option['name'],
            ];
        }

        $columns = array_merge($columns, [
                [
                    'attribute' => 'sku',
                    'contentOptions' => ['style' => 'width:150px; text-align: center']
                ],
                [
                    'attribute' => 'default_sku',
                    'contentOptions' => ['style' => 'width:150px; text-align: center']
                ],
                'cost',
                'price',
                'compare_at_price',
                'inventory_quantity',
                'inventory_policy',
                'inventory_management',
                [
                    'attribute' => 'created_at',
                    'value'     => function ($model) {
                        if ($model->updated_at) {
                            return date('d, M Y', $model->created_at);
                        }
                    }
                ],
                [
                    'attribute' => 'updated_at',
                    'value'     => function ($model) {
                        if ($model->updated_at) {
                            return date('d, M Y', $model->updated_at);
                        }
                    }
                ],
            ]
        );


        return $columns;

    }

    public static function getVariantOptions(Product $product): array
    {
        $productData = $product->getProductData();
        $options = $productData['options'] ?? [];
        $columns = [
            ['class' => 'yii\grid\SerialColumn'],
        ];

        if (!empty($product->productVariants)) {
            if ($product->productVariants[0]->img) {
                $columns[] = [
                    'attribute' => 'img',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::img($model->img, ['style' => 'width:50px; text-align: center']);
                    },
                ];
            }
        }

        foreach ($options as $key => $option) {
            $columns[] = [
                'attribute' => 'option' . ($key + 1),
                'label' => $option['name'],
            ];
        }

        $columns = array_merge($columns, [
                [
                    'attribute' => 'sku',
                    'contentOptions' => ['style' => 'width:150px; text-align: center']
                ],
                'price',
                'compare_at_price',
                'inventory_quantity',
            [
                'attribute' => 'updated_at',
                'label' => 'Change detected at',
                'value'     => function ($model) {
                    if ($model->updated_at) {
                        return date('d, M Y', $model->updated_at);
                    }
                    return null;
                }
            ],
            [
                'attribute' => 'updated_at',
                'label' => 'Monitored at',
                'value' => function (ProductVariant $model) {
                    return $model->product->monitored_at ? date('d, M Y', $model->product->monitored_at) : '';
                }
            ],
            ]
        );

      $columns[] = [
        'header' => 'Actions',
        'class' => ActionColumn::class,
        'template' => '{update} {delete}',
        'buttons' => [
          'update' => function ($url, $model) {
            $additional_attributes = $model->calculateAdditionalAttributes();
            return  "<a
            title='Edit Variant'
            class='btn btn-success shadow btn-xs sharp mr-1 editVariantButton'
            data-id='$model->id' ". $additional_attributes ."
            >
            <iconify-icon icon='solar:pen-broken'></iconify-icon></a>";
          },
          'delete' => function ($url, $model) {
            return  "<a href='/product-variant/delete?id=$model->id'  data-method='post' title='Delete Variant' data-confirm='Are you sure you want delete this variant?' data-toggle='tooltip' class='btn btn-danger shadow btn-xs sharp mr-1 deleteVariant '><iconify-icon icon='solar:trash-bin-minimalistic-broken'></iconify-icon></a>";
          },
        ],
      ];

        return $columns;
    }


}