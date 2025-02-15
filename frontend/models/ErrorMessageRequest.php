<?php


namespace frontend\models;

use yii\base\Model;

/**
 *
 * @property string $message
 * @property int $line
 */
class ErrorMessageRequest extends Model
{
    public $message;
    public $line;
    public $url;

    public function rules()
    {
        return [
            [['message' , 'line'], 'required'],
            [['message', 'url'], 'string'],
            ['line', 'integer'],
        ];
    }

}