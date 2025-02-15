<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "subjects".
 *
 * @property int $id
 * @property string|null $title
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Subject extends \yii\db\ActiveRecord
{
    const NOT_ABLE_TO_IMPORT_PRODUCT_ID = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subjects';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'integer'],
            [['title'], 'string', 'max' => 255],
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
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function getAllSubjects()
    {
        return Subject::find()->select(['title', 'id'])->indexBy('id')->column() +  ['' => 'Other'];

    }
}
