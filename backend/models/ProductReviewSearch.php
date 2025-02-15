<?php

namespace backend\models;

use common\models\Product;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ProductReview;

/**
 * ProductReviewSearch represents the model behind the search form of `common\models\ProductReview`.
 */
class ProductReviewSearch extends ProductReview
{
    public $product_title;
    public $created_at_max;
    public $created_at_min;
    public $status;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id',  'created_at', 'updated_at','user_id', 'status'], 'integer'],
            [['reviewer_name', 'product_title', 'review', 'date', 'created_at_max', 'created_at_min'], 'safe'],
            [['rate'], 'number'],
        ];
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
        $table = self::tableName();
        $query = ProductReview::find();
        $created_at_max = $this->created_at_max ? date("yy-m-d T H:m:s",strtotime($this->created_at_max)) : '';
        $created_at_min = $this->created_at_min ? date("yy-m-d T H:m:s",strtotime($this->created_at_min)) : '';

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['id'=>SORT_DESC]]
        ]);
        
        if (!$this->product_id && !empty($params['product_id'])) {
            $this->product_id = $params['product_id'];
        }
        
        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        if ($this->date) {
            $dateStart = strtotime($this->date);
            $dateEnd = $dateStart + 24*60*60-1;

            $query->where(['>=', $table.'.'.'date', $dateStart])
                ->andWhere(['<=', $table.'.'.'date', $dateEnd]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'product_id' => $this->product_id,
            'rate' => $this->rate,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
             self::tableName().'.user_id' => $this->user_id,
            'created_at_min' => $created_at_min,
            'created_at_max' => $created_at_max,
            'status' => $this->status,
        ]);

        if ($this->product_title) {
            $query->innerJoinWith('product')
                ->andFilterWhere(['like', Product::tableName().'.title', $this->product_title]);
        }

        $query->andFilterWhere(['like', 'reviewer_name', $this->reviewer_name])
            ->andFilterWhere(['like', 'review', $this->review]);

        return $dataProvider;
    }
}
