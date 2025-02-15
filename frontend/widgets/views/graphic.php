<?php
/* @var $sites \common\models\AvailableSite*/
?>
<div class="box box-solid bg-teal-gradient">
    <div class="box-header">
        <i class="fa fa-th"></i>

        <h3 class="box-title">Products statistics by Sites</h3>

        <div class="box-tools pull-right">
            <button type="button" class="btn bg-teal btn-sm" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn bg-teal btn-sm" data-widget="remove"><i class="fa fa-times"></i>
            </button>
        </div>
    </div>
    <div class="box-body border-radius-none hidden">
        <div class="chart" id="line-chart" style="height: 250px;"></div>
    </div>
    <!-- /.box-body -->
    <div class="box-footer no-border">
        <div class="row">
            <?php foreach($sites as $site):?>

            <div class="col-xs-4 text-center" style="border-right: 1px solid #f4f4f4">
                <input type="text" class="knob" data-readonly="true" value=<?= $site->getSiteProductsPercent(Yii::$app->user->identity)?> data-width="60" data-height="60"
                       data-fgColor="#39CCCC">
                <div class="knob-label"><?= $site->name?></div>
            </div>
            <?php endforeach;?>
        </div>
        <!-- /.row -->
    </div>
    <!-- /.box-footer -->
</div>
