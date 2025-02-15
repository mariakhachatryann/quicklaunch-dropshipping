<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "currencies".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $code
 *
 * @property Product[] $products
 * @property Product[] $products0
 */
class Currency extends \yii\db\ActiveRecord
{
    const DEFAULT_CURRENCY = 'USD';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'currencies';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'code'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'code' => 'Code',
        ];
    }

    /**
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Products::class, ['currency_id' => 'id']);
    }

    /**
     * Gets query for [[Products0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts0()
    {
        return $this->hasMany(Products::class, ['default_currency_id' => 'id']);
    }
}
