<?php

namespace frontend\models;

use common\models\AvailableSite;
use common\models\Category;
use common\models\Niche;
use common\models\Product;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%recommended_products}}".
 *
 * @property int $id
 * @property string|null $sku
 * @property int|null $total
 * @property string|null $title
 * @property string|null $image
 * @property string|null $url
 * @property int|null $product_type_id
 * @property string $product_data
 * @property int|null $site_id
 * @property int|null $niche_id
 * @property int|null $category_id
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class RecommendedProduct extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%recommended_products}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['total', 'created_at', 'updated_at', 'site_id', 'product_type_id', 'niche_id', 'category_id'], 'integer'],
            [['sku', 'title', 'image', 'url'], 'string', 'max' => 255],
			[['site_id'], 'exist', 'skipOnError' => true, 'targetClass' => AvailableSite::class, 'targetAttribute' => ['site_id' => 'id']],
        ];
    }

	/**
	 * {@inheritdoc}
	 */
	public function behaviors()
	{
		return [
			TimestampBehavior::class,
		];
	}

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'Sku',
            'total' => 'Total',
            'title' => 'Title',
			'url' => 'Product Url',
			'site_id' => 'Site',
            'niche_id' => 'Niche',
            'category_id' => 'Category',
            'image' => 'Image',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

	public function getProduct(): ActiveQuery
	{
		return $this->hasOne(Product::class, ['sku' => 'sku']);
	}

	public function getSite(): ActiveQuery
	{
		return $this->hasOne(AvailableSite::class, ['id' => 'site_id']);
	}

    public function getNiche(): ActiveQuery
    {
        return $this->hasOne(Niche::class, ['id' => 'niche_id']);
    }

    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }
}
