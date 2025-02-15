<?php

namespace common\models;

use common\models\Product;
use yii\behaviors\TimestampBehavior;

/**
 * ProductBulkImportItem model
 *
 * @property integer $id
 * @property string $url
 * @property integer $product_id
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $bulk_import_id
 * @property Product $product
 * @property BulkImport $bulkImport

 */


class ProductBulkImportItem extends \yii\db\ActiveRecord
{

    const STATUS_PENDING = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_ERROR = 2;

    const STATUS_TEXTS = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_ERROR => 'Error',
        self::STATUS_SUCCESS => 'Success',
    ];


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%product_bulk_import_items}}';
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
            [['url'], 'string'],
            [['url'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['bulk_import_id'], 'exist', 'skipOnError' => true, 'targetClass' => BulkImport::class, 'targetAttribute' => ['bulk_import_id' => 'id']],
            [['url', 'bulk_import_id'], 'required'],
            [['updated_at', 'status'], 'integer'],
            [['created_at', 'product_id'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Url',
            'product_id' => 'Product Id',
            'status' => 'Status',
            'bulk_import_id' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBulkImport()
    {
        return $this->hasOne(BulkImport::class, ['id' => 'bulk_import_id']);
    }

}