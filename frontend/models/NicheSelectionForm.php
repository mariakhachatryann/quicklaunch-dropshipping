<?php

namespace frontend\models;

use yii\base\Model;

class NicheSelectionForm extends Model
{
    public $niche_id;

    public function rules()
    {
        return [
            [['niche_id'], 'required'],
            [['niche_id'], 'integer'],
        ];
    }
}
