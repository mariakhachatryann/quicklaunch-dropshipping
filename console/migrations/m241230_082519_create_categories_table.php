<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%categories}}`.
 */
class m241230_082519_create_categories_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%categories}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'niche_id' => $this->integer(),
            'shop_id' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-categories-niche-id',
            '{{%categories}}',
            'niche_id',
            '{{%niches}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-categories-shop-id' ,
            '{{%categories}}',
            'shop_id',
            '{{%shops}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%categories}}');
    }
}
