<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ProductPublishQueue */

$this->title = 'Create Product Publish Queue';
$this->params['breadcrumbs'][] = ['label' => 'Product Publish Queues', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-publish-queue-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
