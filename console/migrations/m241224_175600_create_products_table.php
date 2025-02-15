<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%products}}`.
 */
class m241224_175600_create_products_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%products}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'user_id' => $this->integer(),
            'src_product_url' => $this->string(),
            'sku' => $this->string(),
            'product_data' => $this->text(),
            'product_type' => $this->string(),
            'product_type_id' => $this->integer(),
            'shopify_id' => $this->integer(),
            'handle' => $this->string(),
            'is_deleted' => $this->boolean()->defaultValue(0),
            'count_variants' => $this->integer()->defaultValue(0),
            'site_id' => $this->integer(),
            'monitored_at' => $this->integer(),
            'monitoring_price' => $this->boolean()->defaultValue(0),
            'monitoring_stock' => $this->boolean()->defaultValue(0),
            'currency_id' => $this->integer(),
            'default_currency_id' => $this->integer(),
            'currency_rate' => $this->double(),
            'update_currency_rate' => $this->integer()->defaultValue(0),
            'imported_from' => $this->integer()->defaultValue(null),
            'monitoring_reviews' => $this->boolean()->defaultValue(false),
            'monitoring_reviews_min_rate' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ],'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');

        $this->addForeignKey(
            'fk-product-user-id',
            '{{%products}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'product-site-id',
            '{{%products}}',
            'site_id',
            '{{%sites}}',
            'id'
        );

        $this->addForeignKey(
            'fk-currency_id_product',
            '{{%products}}',
            'currency_id',
            '{{%currencies}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey('fk-default-currency_id_product',
            '{{%products}}',
            'default_currency_id',
            '{{%currencies}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%products}}');
    }
}
