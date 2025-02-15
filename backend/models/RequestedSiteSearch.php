<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\RequestedSite;

/**
 * RequestedSiteSearch represents the model behind the search form of `common\models\RequestedSite`.
 */
class RequestedSiteSearch extends RequestedSite
{
    public $datetime_max;
    public $datetime_min;

    public $update_datetime_min;
    public $update_datetime_max;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'status'], 'integer'],
            [['url', 'datetime_min', 'datetime_max', 'created_at', 'updated_at',
                'update_datetime_min', 'update_datetime_max'], 'safe'],
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
        $query = RequestedSite::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ],
            'pagination'=>[
                'defaultPageSize' => 50
            ]

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
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'url', $this->url]);

        if ($this->created_at) {
            $query->andFilterWhere(['between', 'created_at', strtotime($this->datetime_min), strtotime($this->datetime_max) + 24*3600 - 1]);
        }

        if ($this->updated_at) {
            $query->andFilterWhere(['between', 'updated_at', strtotime($this->update_datetime_min), strtotime($this->update_datetime_max) + 24*3600 - 1]);
        }

        return $dataProvider;
    }
}
