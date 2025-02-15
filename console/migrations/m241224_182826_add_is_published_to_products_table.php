<?php

use yii\db\Migration;

/**
 * Class m241224_182826_add_is_published_to_products_table
 */
class m241224_182826_add_is_published_to_products_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\common\models\Product::tableName(), 'is_published', $this->tinyInteger()->defaultValue(\common\models\Product::PRODUCT_IS_PUBLISHED));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m241224_182826_add_is_published_to_products_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241224_182826_add_is_published_to_products_table cannot be reverted.\n";

        return false;
    }
    */
}
