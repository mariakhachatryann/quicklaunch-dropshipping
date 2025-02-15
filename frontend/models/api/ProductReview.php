<?php

namespace frontend\models\api;

class ProductReview extends \common\models\ProductReview {

    public function fields()
    {
        $fields = parent::fields();
        $fields['images'] = 'productReviewImages';

        return $fields;
    }
}