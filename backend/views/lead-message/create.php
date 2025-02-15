<?php

use common\models\UploadForm;
use yii\base\BaseObject;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\LeadMessage */
/* @var $leadMessage common\models\LeadMessage */
/* @var $file UploadForm */
/* @var $lead common\models\Lead */

$this->title = 'Answer to '. $lead->user->username;
$this->params['breadcrumbs'][] = ['label' => 'Lead Messages', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="lead-message-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', compact('model', 'lead', 'leadMessage', 'file')) ?>

</div>
