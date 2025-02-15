<?php

namespace frontend\models;

use common\models\Niche;
use common\models\User;
use Yii;

/**
 * This is the model class for table "user_niche".
 *
 * @property int $id
 * @property int $user_id
 * @property int $niche_id
 *
 * @property Niche $niche
 * @property User $user
 */
class UserNiche extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_niche';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'niche_id'], 'required'],
            [['user_id', 'niche_id'], 'integer'],
            [['niche_id'], 'exist', 'skipOnError' => true, 'targetClass' => Niche::class, 'targetAttribute' => ['niche_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'niche_id' => 'Niche ID',
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
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getCategories()
    {
        return $this->niche ? $this->niche->categories : [];
    }
}
