<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%help_texts}}`.
 */
class m241225_134904_create_help_texts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%help_texts}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string(),
            'title' => $this->string(),
            'text' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%help_texts}}');
    }
}
