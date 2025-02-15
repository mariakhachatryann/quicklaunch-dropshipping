<?php

use yii\db\Migration;

/**
 * Class m250110_105917_create_variant_price_markups
 */
class m250110_105917_create_variant_price_markups extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%variant_price_markups}}', [
            'id' => $this->primaryKey(),
            'variant_id' => $this->integer(),
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
            'fk-variant-id-product-price-markups',
            '{{%variant_price_markups}}',
            'variant_id',
            '{{%product_variants}}',
            '{{id}}',
            'CASCADE'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250110_105917_create_variant_price_markups cannot be reverted.\n";

        return false;
    }
    */
}
