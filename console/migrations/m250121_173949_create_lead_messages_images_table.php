<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%lead_messages_images}}`.
 */
class m250121_173949_create_lead_messages_images_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%lead_message_images}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text(),
            'lead_message_id' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk-lead_message_images_id',
            '{{%lead_message_images}}',
            'lead_message_id',
            \common\models\LeadMessage::tableName(),
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%lead_messages_images}}');
    }
}
