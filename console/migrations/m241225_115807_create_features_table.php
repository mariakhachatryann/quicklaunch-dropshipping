<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%features}}`.
 */
class m241225_115807_create_features_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%features}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'icon' => $this->string(),
            'description' => $this->string(),
            'setting_key' => $this->string(),
            'sort' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%features}}');
    }
}
