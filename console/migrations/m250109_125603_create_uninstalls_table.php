<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%uninstalls}}`.
 */
class m250109_125603_create_uninstalls_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%uninstalls}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'plan_id' => $this->integer(),
            'duration' => $this->integer(),
            'uninstalled_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-uninstall-user-id',
            '{{%uninstalls}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-uninstall-plan-id',
            '{{%uninstalls}}',
            'plan_id',
            '{{%plans}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%uninstalls}}');
    }
}
