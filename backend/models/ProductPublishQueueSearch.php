<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ProductPublishQueue;

/**
 * ProductPublishQueueSearch represents the model behind the search form of `common\models\ProductPublishQueue`.
 */
class ProductPublishQueueSearch extends ProductPublishQueue
{
    public $user_id;
    public $dateRangeCreated;
    public $dateRangeUpdated;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'product_id', 'status', 'created_at', 'updated_at','user_id'], 'integer'],
            [['dateRangeCreated', 'dateRangeUpdated'], 'safe'],
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
        $query = ProductPublishQueue::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if($this->dateRangeCreated) {
            list($createdAtStart, $createdAtEnd) = explode(" - ", $this->dateRangeCreated);
            $query->andFilterWhere(['between','product_publish_queues.created_at', strtotime($createdAtStart), strtotime($createdAtEnd)+86400]);
        }
        if($this->dateRangeUpdated) {
            list($updatedAtStart, $updatedAtEnd) = explode(" - ", $this->dateRangeUpdated);
            $query->andFilterWhere(['between','product_publish_queues.updated_at', strtotime($updatedAtStart), strtotime($updatedAtEnd)+86400]);
        }

        if ($this->user_id){
            $query->innerJoinWith(['product' => function ($q) {
                $q->andWhere(['=','products.user_id',$this->user_id]);
            }]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'product_publish_queues.id' => $this->id,
            'product_publish_queues.product_id' => $this->product_id,
            'product_publish_queues.status' => $this->status,
        ]);

        return $dataProvider;
    }
}
