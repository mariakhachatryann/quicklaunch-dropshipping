<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%product_variants}}`.
 */
class m250110_103920_create_product_variants_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%product_variants}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer(),
            'img' => $this->string(),
            'option1' =>$this->string(),
            'option2' => $this->string(),
            'option3' => $this->string(),
            'sku' => $this->string(),
            'default_sku' => $this->string(),
            'price' =>  $this->double(),
            'compare_at_price' => $this->double(),
            'inventory_quantity' => $this->integer(),
            'inventory_item_id' => $this->bigInteger(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'shopify_variant_id' => $this->bigInteger()->unsigned(),
            'cost' => $this->double(),
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');

        $this->addForeignKey(
            'fk-product-id-product-variants',
            '{{%product_variants}}',
            'product_id',
            '{{%products}}',
            'id',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%product_variants}}');
    }
}
