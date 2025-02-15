<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%plans}}`.
 */
class m241224_134646_create_plans_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%plans}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'description' => $this->string(),
            'price' => $this->integer(),
            'trial_days' => $this->integer()->defaultValue(0),
            'product_limit' => $this->integer()->defaultValue(null),
            'monitoring_limit' => $this->integer()->defaultValue(100),
            'review_limit' => $this->integer()->defaultValue(100),
            'is_custom' => $this->boolean()->defaultValue(0),
            'color' => $this->string(),
            'old_price' => $this->double(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%plans}}');
    }
}
