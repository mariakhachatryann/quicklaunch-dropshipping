<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_charges}}`.
 */
class m250109_124821_create_user_charges_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_charges}}', [
            'id' => $this->primaryKey(),
            'charge_id' => $this->bigInteger(),
            'user_id' => $this->integer(),
            'name' => $this->string(),
            'api_client_id' => $this->integer(),
            'price'=> $this->float(),
            'status' => $this->string(),
            'billing_on' => $this->dateTime(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'test' => $this->integer(),
            'activated_on' => $this->dateTime(),
            'canceled_on' => $this->dateTime(),
            'trial_days' => $this->integer(),
            'capped_amount' => $this->float(),
            'trial_ends_a_on' => $this->dateTime(),
            'balance_used' => $this->float(),
            'balance_remaining' => $this->float(),
            'risk_level' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-user-charges-user-id',
            '{{%user_charges}}',
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
        $this->dropTable('{{%user_charges}}');
    }
}
