<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\AvailableSite;

/**
 * AvailableSiteSearch represents the model behind the search form of `common\models\AvailableSite`.
 */
class AvailableSiteSearch extends AvailableSite
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at', 'scrap_internal', 'monitor_available', 'import_by_queue', 'import_by_extension', 'is_new'], 'integer'],
            [['name', 'url'], 'safe'],
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
        $query = AvailableSite::find();

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
            'scrap_internal' => $this->scrap_internal,
            'monitor_available' => $this->monitor_available,
            'import_by_queue' => $this->import_by_queue,
            'import_by_extension' => $this->import_by_extension,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_new' => $this->is_new,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'url', $this->url]);

        return $dataProvider;
    }
}
