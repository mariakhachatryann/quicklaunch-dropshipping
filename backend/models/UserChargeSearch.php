<?php


namespace backend\models;

use common\models\UserCharge;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class UserChargeSearch extends UserCharge
{

    public $username;
    public $billingOnRange;
    public $createdAtRange;
    public $updatedAtRange;
    public $activatedOnRange;
    public $canceledOnRange;
    public $trialEndsAOnRange;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['charge_id', 'user_id', 'api_client_id', 'test', 'trial_days', 'risk_level'], 'integer'],
            [['price', 'capped_amount', 'balance_used', 'balance_remaining'], 'number'],
            [['username',
                'billingOnRange','createdAtRange','updatedAtRange','activatedOnRange','canceledOnRange','trialEndsAOnRange',
                'billing_on', 'created_at', 'updated_at', 'activated_on', 'canceled_on', 'trial_ends_a_on']
                , 'safe'],
            [['name', 'status'], 'string', 'max' => 255],
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
        $query = UserCharge::find();

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
        if($this->billingOnRange) {
            list($billingOnStart, $billingOnEnd) = explode(" - ", $this->billingOnRange);
            $query->andFilterWhere(['between','user_charges.billing_on', $billingOnStart, $billingOnEnd]);
        }
        if($this->createdAtRange) {
            list($createdAtStart, $createdAtEnd) = explode(" - ", $this->createdAtRange);
            $query->andFilterWhere(['between','user_charges.created_at', $createdAtStart, $createdAtEnd]);
        }
        if($this->updatedAtRange) {
            list($updatedAtStart, $updatedAtEnd) = explode(" - ", $this->updatedAtRange);
            $query->andFilterWhere(['between','user_charges.updated_at', $updatedAtStart, $updatedAtEnd]);
        }
        if($this->activatedOnRange) {
            list($activatedOnStart, $activatedOnEnd) = explode(" - ", $this->activatedOnRange);
            $query->andFilterWhere(['between','user_charges.activated_on', $activatedOnStart, $activatedOnEnd]);
        }
        if($this->canceledOnRange) {
            list($canceledOnStart, $canceledOnEnd) = explode(" - ", $this->canceledOnRange);
            $query->andFilterWhere(['between','user_charges.canceled_on', $canceledOnStart, $canceledOnEnd]);
        }
        if($this->trialEndsAOnRange) {
            list($trialEndsAOnStart, $trialEndsAOnEnd) = explode(" - ", $this->trialEndsAOnRange);
            $query->andFilterWhere(['between','user_charges.trial_ends_a_on', $trialEndsAOnStart, $trialEndsAOnEnd]);
        }

        if ($this->username){
            $query->innerJoinWith(['user' => function ($q) {
                $q->andWhere(['like','users.username',$this->username]);
            }]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'user_charges.id' => $this->id,
            'user_charges.charge_id' => $this->charge_id,
            'user_charges.api_client_id' => $this->api_client_id,
            'user_charges.test' => $this->test,
            'user_charges.trial_days' => $this->trial_days,
            'user_charges.risk_level' => $this->risk_level,
        ]);


        $query->andFilterWhere(['like', 'user_charges.name', $this->name])
            ->andFilterWhere(['like', 'user_charges.status', $this->status])
            ->andFilterWhere(['like', 'user_charges.price', $this->price])
            ->andFilterWhere(['like', 'user_charges.capped_amount', $this->capped_amount])
            ->andFilterWhere(['like', 'user_charges.balance_used', $this->balance_used])
            ->andFilterWhere(['like', 'user_charges.balance_remaining', $this->balance_remaining]);



        return $dataProvider;
    }


}