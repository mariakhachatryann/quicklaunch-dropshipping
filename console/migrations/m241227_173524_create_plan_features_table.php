<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%plan_features}}`.
 */
class m241227_173524_create_plan_features_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%plan_features}}', [
            'id' => $this->primaryKey(),
            'plan_id' => $this->integer(),
            'feature_id' => $this->integer(),
        ],'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');


        $this->addForeignKey(
            'fk-plan-id-feature',
            '{{%plan_features}}',
            'plan_id',
            '{{%plans}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-plan-feature-id',
            '{{%plan_features}}',
            'feature_id',
            '{{%features}}',
            'id',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%plan_features}}');
    }
}
