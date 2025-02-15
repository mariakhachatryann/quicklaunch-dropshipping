<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 10.08.2019
 * Time: 12:45
 */

namespace frontend\assets;


use yii\web\AssetBundle;

class PolicyAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css',
        'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic',
        'css/style.css',
        'css/site.css',
    ];

    public $js = [
        //'js/setting.js',

    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
    ];
}