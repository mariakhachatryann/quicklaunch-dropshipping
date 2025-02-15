<?php

use common\models\ImportQueue;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%alert_captchas}}`.
 */
class m250203_073708_create_alert_captchas_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%alert_captchas}}', [
            'id' => $this->primaryKey(),
            'import_queue_id' => $this->integer(),
            'handler' => $this->integer(),
            'status' => $this->integer(),
            'admin_id' => $this->integer()->null(),
            'taken_at' => $this->integer()->null(),
            'duration' => $this->integer()->defaultValue(null),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey('fk-import_queue_id',
            '{{%alert_captchas}}',
            'import_queue_id',
            ImportQueue::tableName(),
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-alert_captchas-admin_id',
            '{{%alert_captchas}}',
            'admin_id',
            '{{%admins}}',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%alert_captchas}}');
    }
}
