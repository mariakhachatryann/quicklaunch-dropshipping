<?php

use common\models\Plan;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%plan_statistics}}`.
 */
class m250109_125832_create_plan_statistics_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%plan_statistics}}', [
            'id' => $this->primaryKey(),
            'plan_id' => $this->integer(),
            'total' => $this->integer()->defaultValue(0),
            'date' => $this->integer(),
        ]);

        $this->addForeignKey('fk_plan_statistics-plan',
            'plan_statistics',
            'plan_id',
            Plan::tableName(),
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
        $this->dropTable('{{%plan_statistics}}');
    }
}
