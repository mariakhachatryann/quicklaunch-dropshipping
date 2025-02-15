<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Product;

/**
 * ProductSearch represents the model behind the search form of `common\models\Product`.
 */
class ProductSearch extends Product
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
            [['id', 'user_id', 'site_id'], 'integer'],
            [['title', 'src_product_url', 'sku', 'shopify_id', 'is_deleted',
				'monitored_at', 'monitoring_price', 'monitoring_stock', 'is_published',
				'datetime_min', 'datetime_max', 'created_at', 'updated_at',
				'update_datetime_min', 'update_datetime_max', 'imported_from'
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
        $query = Product::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ],
            'pagination'=>[
                'defaultPageSize' => 10
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
            'shopify_id' => $this->shopify_id,
            'is_deleted' => $this->is_deleted,
            'monitoring_stock' => $this->monitoring_stock,
            'monitoring_price' => $this->monitoring_price,
            'is_published' => $this->is_published,
			'site_id' => $this->site_id,
			'imported_from' => $this->imported_from,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'src_product_url', $this->src_product_url])
            ->andFilterWhere(['like', 'sku', $this->sku]);

		if ($this->created_at) {
			$query->andFilterWhere(['between', 'created_at', strtotime($this->datetime_min), strtotime($this->datetime_max) + 24*3600 - 1]);
		}

		if ($this->updated_at) {
			$query->andFilterWhere(['between', 'updated_at', strtotime($this->update_datetime_min), strtotime($this->update_datetime_max) + 24*3600 - 1]);
		}
		
		if ($this->monitored_at) {
			$query->andFilterWhere(['between', 'monitored_at', strtotime($this->update_datetime_min), strtotime($this->update_datetime_max) + 24*3600 - 1]);
		}

        return $dataProvider;
    }
}
