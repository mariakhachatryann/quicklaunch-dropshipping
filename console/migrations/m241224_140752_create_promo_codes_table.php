<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%promo_codes}}`.
 */
class m241224_140752_create_promo_codes_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%promo_codes}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->defaultValue(null),
            'code' => $this->string()->unique(),
            'plan_id' => $this->integer(),
            'price' => $this->integer(),
            'active_until' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-user-id-promo-code',
            '{{%promo_codes}}',
            'user_id',
            '{{%users}}',
            '{{id}}',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-plan-id-promo-code',
            '{{%promo_codes}}',
            'plan_id',
            '{{%plans}}',
            '{{id}}',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%promo_codes}}');
    }
}
