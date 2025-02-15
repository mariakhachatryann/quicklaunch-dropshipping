<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "niches".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $description
 * @property int|null $is_trending
 */
class Niche extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'niches';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_trending'], 'integer'],
            [['name', 'description'], 'string', 'max' => 255],
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
            'description' => 'Description',
            'is_trending' => 'Is Trending',
        ];
    }

    public static function getTrendingNiches()
    {
        return self::find()->where(['is_trending' => 1])->all();
    }

    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('user_niche', ['niche_id' => 'id']);
    }

    public function getCategories()
    {
        return $this->hasMany(Category::class, ['niche_id' => 'id']);
    }
}
