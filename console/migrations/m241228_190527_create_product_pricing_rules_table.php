<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%product_pricing_rules}}`.
 */
class m241228_190527_create_product_pricing_rules_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%product_pricing_rules}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'price_min' => $this->double(),
            'price_max' => $this->double(),
            'price_markup' => $this->boolean(),
            'compare_at_price_markup' => $this->boolean(),
            'price_by_percent' => $this->double(),
            'price_by_amount' => $this->double(),
            'compare_at_price_by_amount' => $this->double(),
            'compare_at_price_by_percent' => $this->double(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-product-pricing-rules-user-id',
            '{{%product_pricing_rules}}',
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
        $this->dropTable('{{%product_pricing_rules}}');
    }
}
