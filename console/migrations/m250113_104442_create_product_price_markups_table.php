<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%product_price_markups}}`.
 */
class m250113_104442_create_product_price_markups_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%product_price_markups}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer(),
            'price_markup' => $this->integer(),
            'price_by_percent' => $this->double(),
            'price_by_amount' => $this->double(),
            'compare_at_price_markup' => $this->integer(),
            'compare_at_price_by_amount' => $this->double(),
            'compare_at_price_by_percent' => $this->double(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-product-id-product-price-markups',
            '{{%product_price_markups}}',
            'product_id',
            '{{%products}}',
            '{{id}}',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%product_price_markups}}');
    }
}
