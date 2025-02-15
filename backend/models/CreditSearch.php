<?php


namespace backend\models;


use backend\models\Credit;
use yii\base\BaseObject;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class CreditSearch extends Credit
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'user_id', 'plan_id', 'status',], 'integer'],
            [['error_message'], 'safe'],
            [['shopify_credit_id'], 'string'],
            [['amount'], 'number'],
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
        $query = Credit::find();

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
            'amount' => $this->amount,
            'user_id' => $this->user_id,
            'plan_id' => $this->plan_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'error_message', $this->error_message])
        ->andFilterWhere(['like', 'shopify_credit_id', $this->shopify_credit_id]);

        return $dataProvider;
    }
}