<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%users}}`.
 */
class m241224_140920_add_country_code_column_to_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('users', 'country_code', $this->string());
        $query = new \yii\db\Query();
        $userDetails = \yii\helpers\ArrayHelper::map(
            $query->from('users')->where(['country_code' => null])->orderBy(['id' => SORT_ASC])->all(),
            'id', 'shopify_details');
        foreach ($userDetails as $id => $details) {
            $this->update('users', ['country_code' => json_decode($details)->countryCode], ['country_code' => null, 'id' => $id]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
