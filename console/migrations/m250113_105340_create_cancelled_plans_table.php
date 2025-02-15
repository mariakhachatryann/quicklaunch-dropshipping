<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%cancelled_plans}}`.
 */
class m250113_105340_create_cancelled_plans_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%cancelled_plans}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'cancellation_date' => $this->integer(),
            'status' => $this->boolean()->defaultValue(1),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'user-cancelled-plan',
            '{{%cancelled_plans}}',
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
        $this->dropTable('{{%cancelled_plans}}');
    }
}
