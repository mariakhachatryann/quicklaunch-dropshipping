<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'https://code.iconify.design/2/2.0.0-rc.0/iconify.min.css',
        'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css',
        'css/styles.css',
//        'css/plugins/jsgrid.css',
//        'css/plugins/jsgrid-theme.css',
//        'css/plugins/owl-carousel.css',
        'css/site.css',
        'vendor/sweetalert2/dist/sweetalert2.min.css',
    ];

    public $js = [
        'https://cdn.jsdelivr.net/npm/vue',
        'vendor/global/global.min.js',
        'vendor/bootstrap-select/dist/js/bootstrap-select.min.js',
        'https://code.iconify.design/2/2.0.0-rc.0/iconify.min.js',
        'vendor/toastr/js/toastr.min.js',
        'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js',
//        'js/styleSwitcher.js', //@todo enable for switcher
        'js/plugins-init/toastr-init.js',
        'vendor/sweetalert2/dist/sweetalert2.min.js',
        'vendor/toastr/js/toastr.min.js',
        'js/plugins-init/daterangepicker.js',
        'vendor/bootstrap/dist/js/bootstrap.bundle.min.js',
        'vendor/simplebar/dist/simplebar.min.js',
        'vendor/fullcalendar/index.global.min.js',
        'js/theme/app.init.js',
        'js/theme/theme.js',
        'js/deznav-init.js',
        'js/custom.min.js',
        'js/theme/app.min.js',
        'js/custom-shein-importer.js',
        'js/theme/sidebarmenu-default.js',
        'https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js',
        'vendor/prismjs/prism.js',
        'js/widget/ui-card-init.js'
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
    ];
}
