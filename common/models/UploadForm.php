<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $imageFile;
    public $images;

    public function rules()
    {
        return [
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => ' png, jpg', 'checkExtensionByMimeType' => false],
            [['images'], 'safe'],
        ];
    }

    public function upload($dir)
    {
        if ($this->validate()) {
            $this->imageFile->saveAs(Yii::getAlias('@backend') . '/web/uploads/'.$dir.'/'.$this->imageFile->baseName . '.' . $this->imageFile->extension);
            return true;
        } else {
            return false;
        }
    }
}