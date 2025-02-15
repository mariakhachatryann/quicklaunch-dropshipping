<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "shops".
 *
 * @property int $id
 * @property string $shop_name
 * @property string $access_token
 * @property string|null $plan
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property Categories[] $categories
 */
class Shop extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'shops';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['shop_name', 'access_token'], 'required'],
            [['access_token'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['shop_name', 'plan'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_name' => 'Shop Name',
            'access_token' => 'Access Token',
            'plan' => 'Plan',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Categories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Categories::class, ['shop_id' => 'id']);
    }
}
