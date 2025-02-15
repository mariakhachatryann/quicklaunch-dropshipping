<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ProductPricingRuleSearch represents the model behind the search form of `frontend\models\ProductPricingRule`.
 */
class ProductPricingRuleSearch extends ProductPricingRule
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'user_id', 'price_markup', 'compare_at_price_markup', 'created_at', 'updated_at'], 'integer'],
            [['price_min', 'price_max', 'price_by_percent', 'price_by_amount', 'compare_at_price_by_amount', 'compare_at_price_by_percent'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ProductPricingRule::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'price_min' => $this->price_min,
            'price_max' => $this->price_max,
            'price_markup' => $this->price_markup,
            'compare_at_price_markup' => $this->compare_at_price_markup,
            'price_by_percent' => $this->price_by_percent,
            'price_by_amount' => $this->price_by_amount,
            'compare_at_price_by_amount' => $this->compare_at_price_by_amount,
            'compare_at_price_by_percent' => $this->compare_at_price_by_percent,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        return $dataProvider;
    }
}
