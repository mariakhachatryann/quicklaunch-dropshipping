<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;

/**
 * UserSearch represents the model behind the search form of `common\models\User`.
 */
class UserSearch extends User
{
    public $datetime_min;
    public $datetime_max;

	public $update_datetime_min;
	public $update_datetime_max;

	public $review_datetime_min;
	public $review_datetime_max;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status', 'fail_count'], 'integer'],
            [['username','plan_id', 'plan_status', 'auth_key', 'password_hash',
				'password_reset_token', 'email', 'verification_token', 'country_code',
				'created_at', 'updated_at', 'datetime_min', 'datetime_max', 'update_datetime_min', 'update_datetime_max',
				'has_left_review', 'review_datetime_min', 'review_datetime_max', 'left_review_at', 'is_manual_plan'
			], 'safe'],
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
        $query = User::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=>
                ['defaultOrder' =>
                    ['id'=>SORT_DESC]
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
            'status' => $this->status,
            'plan_id' => $this->plan_id,
            'plan_status' => $this->plan_status,
            'country_code' => $this->country_code,
            'fail_count' => $this->fail_count,
			'has_left_review' => $this->has_left_review,
			'is_manual_plan' => $this->is_manual_plan,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'verification_token', $this->verification_token])
            ->andFilterWhere(['like', 'country_code', $this->country_code]);

		if ($this->created_at) {
			$query->andFilterWhere(['between', 'created_at', strtotime($this->datetime_min), strtotime($this->datetime_max) + 24*3600 - 1]);
		}

		if ($this->updated_at) {
			$query->andFilterWhere(['between', 'updated_at', strtotime($this->update_datetime_min), strtotime($this->update_datetime_max) + 24*3600 - 1]);
		}

		if ($this->left_review_at) {
			$query->andFilterWhere(['between', 'left_review_at', strtotime($this->review_datetime_min), strtotime($this->review_datetime_max) + 24*3600 - 1]);
		}

        return $dataProvider;
    }
}
