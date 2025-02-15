<?php

namespace console\jobs;

use common\models\Product;
use common\models\User;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class ProductDeleteJob extends BaseObject implements JobInterface
{

    public $shopifyId;
    public $userId;


    public function execute($queue)
    {
        $user = User::findOne($this->userId);
        $user->getShopifyApi()->getProductManager()->remove($this->shopifyId);
    }
}