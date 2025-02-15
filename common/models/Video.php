<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "videos".
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $video_url
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Video extends \yii\db\ActiveRecord
{
    const IMPORT_VIDEO_PRICE  = 8;
    const IMPORT_VIDEO_VARIANTS  = 9;

    const IMPORT_VIDEO_BULK_IMPORT = 12;
    const IMPORT_VIDEO_MULTIPLE_IMPORT = 12;

    const IMPORT_VIDEO_IDS = [6, 7];
    const IMPORT_SPECIFIC_VIDEO_IDS = [self::IMPORT_VIDEO_PRICE, self::IMPORT_VIDEO_VARIANTS];
    const IMPORT_MULTIPLE_VIDEO_IDS = [self::IMPORT_VIDEO_BULK_IMPORT, self::IMPORT_VIDEO_MULTIPLE_IMPORT];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'videos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'description', 'video_url'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
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
            'video_url' => 'Video Url',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getYoutubeId()
    {
        $url = $this->video_url;
        $url_string = parse_url($url, PHP_URL_QUERY);
        parse_str($url_string, $args);

        return isset($args['v']) ? $args['v'] : false;

    }
}
