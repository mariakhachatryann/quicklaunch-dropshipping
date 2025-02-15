<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 13.04.2019
 * Time: 10:46
 */

namespace backend\models;

use common\models\AlertCaptcha;
use common\models\CaptchaSearch;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\behaviors\TimestampBehavior;
use yii\base\NotSupportedException;

/**
 * Admin model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $verification_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $role_type
 * @property integer $created_at
 * @property integer $updated_at
 * @property boolean $is_online
 */
class Admin extends ActiveRecord implements IdentityInterface
{
    const SCENARIO_CREATE = 'create';

    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    const ROLE_SUPPORT = 0;
    const ROLE_ADMIN = 1;
    const ROLE_CAPTCHA_SOLVER = 2;

    const ROLES = [
        self::ROLE_SUPPORT => 'Support',
        self::ROLE_ADMIN => 'SuperAdmin',
        self::ROLE_CAPTCHA_SOLVER => 'CaptchaSolver',
    ];

    const STATUSES = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_DELETED => 'Deleted',
        self::STATUS_INACTIVE => 'Inactive',
    ];

    public $password;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admins}}';
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
    public function rules()
    {
        return [
            [['username', 'email'], 'required'],
            ['password', 'required', 'on' => self::SCENARIO_CREATE],
            ['password', 'safe'],
            [['username'], 'string'],
            [['is_online'], 'boolean'],
            ['email', 'email'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
            ['role_type', 'default', 'value' => self::ROLE_SUPPORT],
            ['role_type', 'in', 'range' => [self::ROLE_ADMIN, self::ROLE_SUPPORT, self::ROLE_CAPTCHA_SOLVER]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token)
    {
        return static::findOne([
            'verification_token' => $token,
            'status' => self::STATUS_INACTIVE
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function isAdmin(): bool
    {
        return $this->role_type == self::ROLE_ADMIN;
    }

    public function getFailedCaptchasCount()
    {
        return AlertCaptcha::find()
            ->where(['admin_id' => $this->id])
            ->andWhere(['not in', 'status', [AlertCaptcha::STATUS_SOLVED, AlertCaptcha::STATUS_PENDING]])
            ->count();
    }

    public function getLastAlertCaptcha()
    {
        return $this->hasOne(AlertCaptcha::class, ['admin_id' => 'id'])
            ->orderBy(['taken_at' => SORT_DESC]);
    }

    public function getAverageDuration(CaptchaSearch $searchModel = null)
    {
        $solverDuration = AlertCaptcha::calculateAverageDurationPerAdmin($searchModel);

        return isset($solverDuration[$this->id]) ? $solverDuration[$this->id] : null;
    }

    public static function getOnlineCaptchaSolversCount(): int
    {
        return Admin::find()->where(['role_type' => Admin::ROLE_CAPTCHA_SOLVER, 'is_online' => 1])->count();
    }

}