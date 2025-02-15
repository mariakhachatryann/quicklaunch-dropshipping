<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_niche}}`.
 */
class m241230_085417_create_user_niche_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_niche}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'niche_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk_user_niche_user_id',
            'user_niche',
            'user_id',
            'users',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_user_niche_niche_id',
            'user_niche',
            'niche_id',
            'niches',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_niche}}');
    }
}
