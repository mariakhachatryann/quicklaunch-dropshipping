<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ProductPublishQueue */

$this->title = 'Update Product Publish Queue: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Product Publish Queues', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="product-publish-queue-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
