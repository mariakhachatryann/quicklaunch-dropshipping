<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ProductVariant */

$this->title = 'Update Product Variant: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Product Variants', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="product-variant-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
