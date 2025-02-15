<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%subjects}}`.
 */
class m241225_130128_create_subjects_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%subjects}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%subjects}}');
    }
}
