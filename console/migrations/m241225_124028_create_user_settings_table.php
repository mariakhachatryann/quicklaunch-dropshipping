<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_settings}}`.
 */
class m241225_124028_create_user_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_settings}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'price_markup' => $this->boolean()->defaultValue(0),
            'price_amount' => $this->double(),
            'price_percentage' => $this->double(),
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');

        $this->addForeignKey(
            'fk-user-setting-user-id',
            '{{%user_settings}}',
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
        $this->dropTable('{{%user_settings}}');
    }
}
