<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ProductVariant */

$this->title = 'Create Product Variant';
$this->params['breadcrumbs'][] = ['label' => 'Product Variants', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-variant-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
