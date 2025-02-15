<?php

use yii\db\Migration;

/**
 * Class m250108_083404_add_niche_id_category_id_to_products_table
 */
class m250108_083404_add_niche_id_category_id_to_products_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\common\models\Product::tableName(), 'niche_id', $this->integer());
        $this->addColumn(\common\models\Product::tableName(), 'category_id', $this->integer());

        $this->addForeignKey(
            'fk_products_niche_id',
            \common\models\Product::tableName(),
            'niche_id',
            \common\models\Niche::tableName(),
            'id'
        );

        $this->addForeignKey(
            'fk_products_category_id',
            \common\models\Product::tableName(),
            'category_id',
            \common\models\Category::tableName(),
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250108_083404_add_niche_id_category_id_to_products_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250108_083404_add_niche_id_category_id_to_products_table cannot be reverted.\n";

        return false;
    }
    */
}
