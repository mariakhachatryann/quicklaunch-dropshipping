<?php

use yii\db\Migration;

/**
 * Class m250211_120213_add_origin_data_and_description_field_to_recommended_products_table
 */
class m250211_120213_add_origin_data_and_description_field_to_recommended_products_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('recommended_products', 'product_data', 'LONGTEXT');
        $this->addColumn('recommended_products', 'description', $this->text()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250211_120213_add_origin_data_and_description_field_to_recommended_products_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250211_120213_add_origin_data_and_description_field_to_recommended_products_table cannot be reverted.\n";

        return false;
    }
    */
}
