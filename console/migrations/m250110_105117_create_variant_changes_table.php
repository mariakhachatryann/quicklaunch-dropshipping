<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%variant_changes}}`.
 */
class m250110_105117_create_variant_changes_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%variant_changes}}', [
            'id' => $this->primaryKey(),
            'variant_id' => $this->integer(),
            'new_price' => $this->double(),
            'old_price' => $this->double(),
            'new_compare_at_price' => $this->double(),
            'old_compare_at_price' => $this->double(),
            'old_inventory_quantity' => $this->integer(),
            'new_inventory_quantity' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');


        $this->addForeignKey(
            'fk-variant-id-variant-changes',
            '{{%variant_changes}}',
            'variant_id',
            '{{%product_variants}}',
            'id',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%variant_changes}}');
    }
}
