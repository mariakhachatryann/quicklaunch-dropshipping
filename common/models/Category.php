<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "categories".
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $niche_id
 * @property int|null $shop_id
 *
 * @property Niche $niche
 * @property Post[] $posts
 * @property Shop $shop
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'categories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['niche_id', 'shop_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['niche_id'], 'exist', 'skipOnError' => true, 'targetClass' => Niche::class, 'targetAttribute' => ['niche_id' => 'id']],
            [['shop_id'], 'exist', 'skipOnError' => true, 'targetClass' => Shop::class, 'targetAttribute' => ['shop_id' => 'id']],
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
            'niche_id' => 'Niche ID',
            'shop_id' => 'Shop ID',
        ];
    }

    /**
     * Gets query for [[Niche]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNiche()
    {
        return $this->hasOne(Niche::class, ['id' => 'niche_id']);
    }

    /**
     * Gets query for [[Posts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Post::class, ['category_id' => 'id']);
    }

    /**
     * Gets query for [[Shop]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::class, ['id' => 'shop_id']);
    }

    static function categoryPostsFirstItem()
    {
        return self::find()->one();
    }

    public static function allCategories()
    {
        return Category::find()->select(['name', 'id'])
            ->indexBy('id')->column();
    }}
