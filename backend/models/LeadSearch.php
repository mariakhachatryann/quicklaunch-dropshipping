<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Lead;

/**
 * LeadSearch represents the model behind the search form of `common\models\Lead`.
 */
class LeadSearch extends Lead
{
    public $username;
    public $datetime_max;
    public $datetime_min;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'subject_id', 'user_id', 'status', 'updated_at'], 'integer'],
            [['message', 'image', 'username', 'created_at', 'datetime_min', 'datetime_max'], 'safe'],
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
        $query = Lead::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['updated_at'=>SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($this->username){
            $query->innerJoinWith(['user' => function ($q) {
                $q->andWhere(['like','username',$this->username]);
            }]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'subject_id' => $this->subject_id,
            'status' => $this->status,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'message', $this->message])
            ->andFilterWhere(['like', 'image', $this->image]);

        if ($this->created_at) {
            $query->andFilterWhere(['between', 'created_at', strtotime($this->datetime_min), strtotime($this->datetime_max) + 24*3600 - 1]);
        }

        return $dataProvider;
    }
}
