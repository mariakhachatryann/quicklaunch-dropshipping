<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;

/**
 * BulkImport model
 *
 * @property integer $id
 * @property string $category_url
 * @property integer $user_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property User[] $user
 * @property ProductBulkImportItem[] $productBulkImportItems

 */


class BulkImport extends \yii\db\ActiveRecord
{

    const FROM_EXTENSION = 1;

    const MAX_IMPORT_TIME = 60;

    public $productsToPublish;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bulk_imports}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_url'], 'string', 'max' => 600],
            [['category_url'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['user_id', 'category_url'], 'required'],
            [['productsToPublish'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_url' => 'Category Url',
            'user_id' => 'User Id',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductBulkImportItems()
    {
        return $this->hasMany(ProductBulkImportItem::class, ['bulk_import_id' => 'id']);
    }

}