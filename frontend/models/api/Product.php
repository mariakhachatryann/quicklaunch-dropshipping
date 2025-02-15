<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 16.04.2019
 * Time: 14:11
 */

namespace frontend\models\api;


class Product extends \common\models\Product
{
    public function fields()
    {
        $fields = parent::fields();
        $fields['created_at'] = 'createdAt';
        $fields['updated_at'] = 'updatedAt';

        unset($fields['user_id']);
        return $fields;
    }

    public function getCreatedAt()
    {
        return date('Y-m-d H:i:s', $this->created_at);
    }

    public function getUpdatedAt()
    {
        return date('Y-m-d H:i:s', $this->updated_at);
    }

}