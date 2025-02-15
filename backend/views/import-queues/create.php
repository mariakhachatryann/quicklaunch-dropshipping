<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ImportQueue */

$this->title = 'Create Import Queues';
$this->params['breadcrumbs'][] = ['label' => 'Import Queues', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="import-queues-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
