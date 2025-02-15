<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%faq_categories}}`.
 */
class m250116_124612_create_faq_categories_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%faq_categories}}', [
            'id' => $this->primaryKey(),
            'title' =>$this->text()->notNull(),
            'parent_id' =>$this->integer(),
            'description' => $this->text(),
            'sort' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%faq_categories}}');
    }
}
