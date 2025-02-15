<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ProductVariant;

/**
 * ProductVariantSearch represents the model behind the search form of `common\models\ProductVariant`.
 */
class ProductVariantSearch extends ProductVariant
{

    public $updated_at_max;
    public $updated_at_min;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'product_id', 'inventory_quantity', 'inventory_item_id', 'updated_at', 'shopify_variant_id'], 'integer'],
            [['img', 'option1', 'option2', 'option3', 'sku', 'default_sku', 'updated_at_max', 'updated_at_min'], 'safe'],
            [['price', 'compare_at_price'], 'number'],
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
        $query = ProductVariant::find();

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

        $created_at_max = $this->updated_at_max ? date("yy-m-d T H:m:s",strtotime($this->updated_at_max)) : '';
        $created_at_min = $this->updated_at_min ? date("yy-m-d T H:m:s",strtotime($this->updated_at_min)) : '';

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'product_id' => $this->product_id,
            'price' => $this->price,
            'compare_at_price' => $this->compare_at_price,
            'inventory_quantity' => $this->inventory_quantity,
            'inventory_item_id' => $this->inventory_item_id,
            'updated_at' => $this->updated_at,
            'shopify_variant_id' => $this->shopify_variant_id,
        ]);

        $query->andFilterWhere(['like', 'img', $this->img])
            ->andFilterWhere(['like', 'option1', $this->option1])
            ->andFilterWhere(['like', 'option2', $this->option2])
            ->andFilterWhere(['like', 'option3', $this->option3])
            ->andFilterWhere(['like', 'sku', $this->sku])
            ->andFilterWhere(['like', 'default_sku', $this->default_sku]);

        return $dataProvider;
    }
}
