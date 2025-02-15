<?php


namespace frontend\controllers\api;


use common\models\ProductUrl;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

class ScrapController extends Controller
{
    public function actionSheinVariant($url=null)
    {
        if (!$url) {
            throw new NotFoundHttpException('Not found');
        }
        
        $url = str_replace('www.shein.com', 'us.shein.com', $url);
        $url .= '?_ver=1.1.8&template=1';
        
        $productUrl = new ProductUrl(compact('url'));
        if ($productUrl->validate()) {
            sleep(rand(1, 3));
            $productUrl->addSheinHeader = true;
            return json_decode($productUrl->getPageContent());
        }

    }

}