<?php


namespace backend\models;

use common\models\MonitoringQueue;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class MonitoringQueueSearch extends MonitoringQueue
{
    public $dateRangeCreated;
    public $dateRangeUpdated;
    public $product_url;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'product_id', 'status','created_at', 'updated_at'], 'integer'],
            [['dateRangeCreated', 'dateRangeUpdated', 'product_url'], 'safe'],
            [['error_msg'], 'string']
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
        $query = MonitoringQueue::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if($this->dateRangeCreated) {
            list($createdAtStart, $createdAtEnd) = explode(" - ", $this->dateRangeCreated);
            $query->andFilterWhere(['between','created_at', strtotime($createdAtStart), strtotime($createdAtEnd)+86400]);
        }
        if($this->dateRangeUpdated) {
            list($updatedAtStart, $updatedAtEnd) = explode(" - ", $this->dateRangeUpdated);
            $query->andFilterWhere(['between','updated_at', strtotime($updatedAtStart), strtotime($updatedAtEnd)+86400]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            MonitoringQueue::tableName(). '.id' => $this->id,
            MonitoringQueue::tableName(). '.product_id' => $this->product_id,
            MonitoringQueue::tableName(). '.status' => $this->status,
        ]);

        if ($this->product_url) {
            $query->innerJoinWith(['product' => function (ActiveQuery $q) {
                $q->andWhere(['LIKE', 'src_product_url', $this->product_url]);
            }]);
        }

        $query->andFilterWhere(['like', 'error_msg', $this->error_msg]);

        return $dataProvider;
    }

}