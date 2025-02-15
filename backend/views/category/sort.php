<?php
/* @var $dataProvider*/
use kartik\sortinput\SortableInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $model common\models\Feature */
//$items = $dataProvider->getModels();

$this->title = 'Sort FAQ: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Features', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Sort';
?>


<div class="feature-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'sort')->widget(
        \kartik\sortinput\SortableInput::class, ['items' => $items])->label(false); ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>




