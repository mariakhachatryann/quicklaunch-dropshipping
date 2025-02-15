<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%lead_messages}}`.
 */
class m250121_154149_create_lead_messages_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%lead_messages}}', [
            'id' => $this->primaryKey(),
            'lead_id' => $this->integer(),
            'user_id' => $this->integer(),
            'message' => $this->text(),
            'image' => $this->text(),
            'status' => $this->smallInteger()->defaultValue(0),
            'sender' => $this->boolean()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-lead_message-lead_id',
            '{{%lead_messages}}',
            'lead_id',
            '{{%leads}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-lead_message-user-id',
            '{{%lead_messages}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%lead_messages}}');
    }
}
