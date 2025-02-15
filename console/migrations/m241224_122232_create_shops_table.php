<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%shops}}`.
 */
class m241224_122232_create_shops_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%shops}}', [
            'id' => $this->primaryKey(),
            'shop_name' => $this->string()->notNull(),
            'access_token' => $this->text()->notNull(),
            'plan' => $this->string(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%shops}}');
    }
}
