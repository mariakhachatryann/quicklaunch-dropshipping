<?php

use yii\db\Migration;

/**
 * Class m250108_093700_add_niche_id_category_id_to_recommended_products_table
 */
class m250108_093700_add_niche_id_category_id_to_recommended_products_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\frontend\models\RecommendedProduct::tableName(), 'niche_id', $this->integer());
        $this->addColumn(\frontend\models\RecommendedProduct::tableName(), 'category_id', $this->integer());

        $this->addForeignKey(
            'fk_recommended_products_niche_id',
            \frontend\models\RecommendedProduct::tableName(),
            'niche_id',
            \common\models\Niche::tableName(),
            'id'
        );

        $this->addForeignKey(
            'fk_recommended_products_category_id',
            \frontend\models\RecommendedProduct::tableName(),
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
        echo "m250108_093700_add_niche_id_category_id_to_recommended_products_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250108_093700_add_niche_id_category_id_to_recommended_products_table cannot be reverted.\n";

        return false;
    }
    */
}
