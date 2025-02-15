<?php

use yii\db\Migration;

/**
 * Class m241224_140835_add_promo_code_id_to_users_table
 */
class m241224_140835_add_promo_code_id_to_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\common\models\User::tableName(),'promo_code_id', $this->integer()->defaultValue(null));

        $this->addForeignKey(
            'fk-promo-code-id-user',
            '{{%users}}',
            'promo_code_id',
            '{{%promo_codes}}',
            '{{id}}',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m241224_140835_add_promo_code_id_to_users_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241224_140835_add_promo_code_id_to_users_table cannot be reverted.\n";

        return false;
    }
    */
}
