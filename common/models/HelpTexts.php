<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "help_texts".
 *
 * @property int $id
 * @property string|null $key
 * @property string|null $title
 * @property string|null $text
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class HelpTexts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'help_texts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['text'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['key', 'title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'title' => 'Title',
            'text' => 'Text',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
