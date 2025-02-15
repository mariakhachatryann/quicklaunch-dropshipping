<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%faq_categories}}".
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property int $created_at
 * @property int $updated_at
 * @property int $sort
 *
 * @property Category[] $childCategories
 * @property Post[] $posts
 */
class FaqCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%faq_categories}}';
    }

    public static function allCategories()
    {
        return FaqCategory::find()->select(['title', 'id'])
            ->indexBy('id')->column();
    }

    public static function categoryPostsItems()
    {
        $categories = self::find()->with(['childCategories', 'posts'])->orderBy('sort')->where(['parent_id' => 0])->all();
        $items = [];
        foreach ($categories as $category) {
            /* @var Category $category */
            if (!$category->childCategories) {
                $items[] = [
                    'label' => $category->title,
                    'icon' => 'circle-o',
                    'url' => ['/site/faq-category/'.$category->id],
                ];
            } else {
                $item = [
                    'label' => $category->title,
                    'icon' => 'circle-o',
                    'url' => ['/site/faq-category/'.$category->id],
                    'items' => []
                ];

                foreach ($category->childCategories as $childCategory) {
                    $item['items'][] = [
                        'label' => $childCategory->title,
                        'icon' => 'circle-o',
                        'url' => ['/site/faq-category/'.$childCategory->id],
                    ];
                }
                $items[] = $item;

            }
        }
        $items[] = [
            'label' => 'Other',
            'icon' => 'circle-o',
            'url' => ['/site/faq-category/'],
        ];
        return $items;
    }

    static function categoryPostsFirstItem()
    {
        return self::find()->one();
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
            [['title', 'description'], 'string'],
            [['created_at', 'updated_at', 'parent_id', 'sort'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'parent_id' => 'Parent category',
            'sort' => 'Sort',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getMenuPosts()
    {
        $items = [];
        foreach ($this->posts as $post) {
            $items[] = [
                'label' => $post->title,
                'icon' => 'circle-o',
                'url' => ['site/post/' . $post->id]
            ];
        }
        return $items;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Post::class, ['faq_category_id' => 'id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParentCategory()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildCategories()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id']);
    }

    public function checkAdminActiveMenu($controller, $action, $param = [])
    {
        if (Yii::$app->controller->id == $controller && Yii::$app->controller->action->id == $action && Yii::$app->getRequest()->queryParams == $param)
        {
            return true;
        } else {
            return false;
        }
    }


}
