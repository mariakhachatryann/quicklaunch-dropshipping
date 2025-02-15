<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%requested_sites}}`.
 */
class m241227_173859_create_requested_sites_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%requested_sites}}', [
            'id' => $this->primaryKey(),
            'url' => $this->string(),
            'user_id' => $this->integer(),
            'status' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-requested_sites-user-id',
            '{{%requested_sites}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%requested_sites}}');
    }
}
