<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%email_templates}}`.
 */
class m241229_123547_create_email_templates_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%email_templates}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string()->notNull(),
            'subject' => $this->string()->notNull(),
            'content' => $this->text()->notNull(),
            'status' => $this->boolean()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%email_templates}}');
    }
}
