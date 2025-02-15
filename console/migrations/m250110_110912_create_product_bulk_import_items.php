<?php

use yii\db\Migration;

/**
 * Class m250110_110912_create_product_bulk_import_items
 */
class m250110_110912_create_product_bulk_import_items extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%product_bulk_import_items}}', [
            'id' => $this->primaryKey(),
            'url' => $this->string(),
            'product_id' => $this->integer(),
            'bulk_import_id' => $this->integer(),
            'status' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey('fk-bulk_imports_item-product_id-product-id',
            '{{%product_bulk_import_items}}',
            'product_id',
            \common\models\Product::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey('fk-bulk_imports-bulk_import_id-bulk_import-id',
            '{{%product_bulk_import_items}}',
            'bulk_import_id',
            \common\models\BulkImport::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250110_110912_create_product_bulk_import_items cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250110_110912_create_product_bulk_import_items cannot be reverted.\n";

        return false;
    }
    */
}
