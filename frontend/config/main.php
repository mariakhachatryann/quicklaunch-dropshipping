<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'name' => 'QuickLaunch Dropshipping',
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
            'baseUrl' => '',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                'login' => 'site/login',
                'products' => 'product/index',
                'orders' => 'order/index',
                'product-review' => 'product-review/index',
                'site/post/<id:\d+>' => 'site/post',
                'site/faq-category/<id:\d+>/<post_id:\d+>' => 'site/faq-category',
                'site/faq-category/<id:\d+>' => 'site/faq-category',
                'api/product/by-sku/<sku:\w+>' => 'api/product/by-sku',
                'api/product/get-product-reviews/<productId:\w+>' => 'api/product/get-product-reviews',
                'notification/<id:\w+>' => 'notifications/view',
                'site/callback/charge' => 'profile/charge',
                'site/chat/<lead_id:\d+>' => 'site/chat',
                'product/<id:\d+>' => 'product/view',
                'order/<id:\d+>' => 'order/view',
                '<controller:\w+>/<action:\w+>/' => '<controller>/<action>',
            ],
        ],

    ],
    'params' => $params,
];
