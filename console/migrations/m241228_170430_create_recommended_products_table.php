<?php

use common\models\ProductType;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%recommended_products}}`.
 */
class m241228_170430_create_recommended_products_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%recommended_products}}', [
            'id' => $this->primaryKey(),
            'sku' => $this->string(),
            'title' => $this->string(),
            'image' => $this->string(),
            'site_id' => $this->integer(),
            'url' => $this->string(),
            'product_type_id' => $this->integer(),
            'total' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey('fk-recommended-product_type',
            '{{%recommended_products}}',
            'product_type_id',
            ProductType::tableName(),
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey('fk-recommended_products-site',
            '{{%recommended_products}}',
            'site_id',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('index-recommended_products-sku', '{{%recommended_products}}', 'sku');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%recommended_products}}');
    }
}
