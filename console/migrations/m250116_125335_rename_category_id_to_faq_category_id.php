<?php

use yii\db\Migration;

/**
 * Class m250116_125335_rename_category_id_to_faq_category_id
 */
class m250116_125335_rename_category_id_to_faq_category_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-post-category-id', \common\models\Post::tableName());
        $this->renameColumn(\common\models\Post::tableName(), 'category_id', 'faq_category_id');
        $this->addForeignKey(
            'fk-post-faq-category_id',
            \common\models\Post::tableName(),
            'faq_category_id',
            'faq_categories',
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
        echo "m250116_125335_rename_category_id_to_faq_category_id cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250116_125335_rename_category_id_to_faq_category_id cannot be reverted.\n";

        return false;
    }
    */
}
