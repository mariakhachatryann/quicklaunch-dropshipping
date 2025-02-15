<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%plan_charge_requests}}`.
 */
class m241229_131245_create_plan_charge_requests_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%plan_charge_requests}}', [
            'id' => $this->primaryKey(),
            'chargeId' => $this->bigInteger(),
            'user_id' => $this->integer(),
            'plan_id' => $this->integer(),
            'status' => $this->boolean()->defaultValue(0),
            'activated_on' => $this->integer(),
            'response_data' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');

        $this->addForeignKey(
            'fk-pcr-user-id',
            '{{%plan_charge_requests}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-pcr-plan-id',
            '{{%plan_charge_requests}}',
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
        $this->dropTable('{{%plan_charge_requests}}');
    }
}
