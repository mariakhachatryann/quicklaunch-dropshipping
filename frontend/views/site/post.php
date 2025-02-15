<?php
/* @var $post \common\models\Post*/
$this->title = $post->title;
?>
<div class="row">
    <ol class="breadcrumb">
        <li><a href="/">Category</a></li>
        <li class="active"><a href="/site/category/<?= $post->category_id ? $post->category->id : ''?>"><?= $post->category_id ? $post->category->title : 'Other'?></a></li>
    </ol>
</div>

<div class="row">
    <!-- ./col -->
    <div class="col-md-12">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title"><?= $post->title ?></h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <blockquote>
                    <p><?= $post->content ?></p>
                    <cite class="pull-right"><?= date('Y-m-d',$post->created_at )?></cite>
                </blockquote>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <!-- ./col -->
</div>


