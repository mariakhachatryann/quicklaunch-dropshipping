<?php


namespace backend\models;


use common\models\Category;
use common\models\Niche;
use frontend\models\RecommendedProduct;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class RecommendedProductSearch extends RecommendedProduct
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_type_id', 'site_id', 'title'], 'safe'],
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
        $query = RecommendedProduct::find();

        $query->joinWith('category')->innerJoin('user_niche', 'user_niche.niche_id = categories.niche_id');
        $query->limit(100);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
            ],
            'pagination' => [
                'defaultPageSize' => 50,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'site_id' => $this->site_id,
        ]);

        if (!empty($this->category_id)) {
            $query->andWhere(['categories.id' => $this->category_id]);
        }

        $query->andFilterWhere(['like', 'title', $this->title]);

        if (!empty($params['user_id'])) {
            $query->andWhere(['user_niche.user_id' => $params['user_id']]);
        }

        if (!empty($params['categoryId'])) {
            $category = Category::find()->where(['id' => $params['categoryId']])->one();
            if ($category) {
                $query->andWhere(['categories.id' => $category->id]);
            }
        }

        return $dataProvider;
    }
}