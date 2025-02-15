<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "email_templates".
 *
 * @property int $id
 * @property string $key
 * @property string $subject
 * @property string $content
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class EmailTemplate extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;


    const USER_INSTALL = 'install';
    const UNINSTALL = 'uninstall';
    const SUBSCRIBE = 'subscribe';
    const UNSUBSCRIBE = 'unsubscribe';
    const FIRST_PRODUCT = 'first_product_import';
    const MORE_THAN_10 = 'imported_more_10_products';
    const NEW_REVIEW = 'new_review';
    const PLAN_CHARGE = 'plan_charge';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'email_templates';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key', 'subject', 'content'], 'required'],
            [['content'], 'string'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['key', 'subject'], 'string', 'max' => 255],
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
            'subject' => 'Subject',
            'content' => 'Content',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function sendEmail(User $user)
    {
        $from = [Yii::$app->params['noReplyEmail'] => Yii::$app->params['appName']];
        $content = Yii::$app->view->render('/email/email-layout', ['template' => $this]);


        try {
            return \Yii::$app->mailer->compose()
                ->setTo('ghazaryan.gohar13@gmail.com')
                ->setFrom($from)
                ->setSubject($this->subject)
                ->setHtmlBody($content)
                ->send();
        } catch (\Swift_SwiftException $exception) {
            Yii::error($exception->getMessage(), 'email errors');
            return false;
        }

    }
    public static function sendByKey($key, User $user)
    {
        $template = self::findOne(['key' => $key, 'status' => self::STATUS_ACTIVE]);
        if ($template) {
            $template->sendEmail($user);
        }

    }

}
