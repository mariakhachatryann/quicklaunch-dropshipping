<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 31.05.2019
 * Time: 14:43
 */

namespace frontend\assets;


use yii\web\AssetBundle;
use yii\web\JqueryAsset;

class LoginAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
//        'css/style.css',
    ];
    public $js = [
        'js/login.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
    ];

}