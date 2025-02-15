<?php

namespace backend\assets;

use dmstr\web\AdminLteAsset;
use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
    ];
    public $js = [
        'js/script.js?v=1',
    ];
    public $depends = [
        AdminLteAsset::class,
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
