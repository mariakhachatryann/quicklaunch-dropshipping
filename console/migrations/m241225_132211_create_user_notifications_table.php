<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_notifications}}`.
 */
class m241225_132211_create_user_notifications_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_notifications}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'notification_id' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-user-id-notification',
            '{{%user_notification}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-user-notification-id',
            '{{%user_notification}}',
            'notification_id',
            '{{%notifications}}',
            'id',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_notifications}}');
    }
}
