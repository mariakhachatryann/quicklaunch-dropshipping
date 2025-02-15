<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\AlertCaptcha;

/**
 * AlertCaptchaSearch represents the model behind the search form of `common\models\AlertCaptcha`.
 * @property int $site_id
 * @property int $country
 * @property int $type
 * @property string $url
 * @property int $status
 * @property int $datetime_min
 * @property int $datetime_max
 * @property int $update_datetime_min
 * @property int $update_datetime_max
 * @property string $processing_ip
 * @property int $admin_id
 */
class AlertCaptchaSearch extends AlertCaptcha
{

    public $site_id;
    public $url;
    public $country;
    public $type;
    public $processing_ip;

    public $admin_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'import_queue_id', 'site_id', 'country','handler', 'status', 'type', 'admin_id'], 'integer'],
            [['url', 'datetime_min', 'datetime_max', 'created_at', 'updated_at', 'update_datetime_min', 'update_datetime_max', 'processing_ip'], 'safe'],
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
        $query = AlertCaptcha::find()->joinWith('importQueue iq')->joinWith('admin adminAlias');

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
            'alert_captchas.id' => $this->id,
            'import_queue_id' => $this->import_queue_id,
            'alert_captchas.created_at' => $this->created_at,
            'alert_captchas.updated_at' => $this->updated_at,
            'alert_captchas.handler' => $this->handler,
            'alert_captchas.status' => $this->status,
            'iq.type' => $this->type,
            'iq.site_id' => $this->site_id,
            'iq.country' => $this->country,
            'adminAlias.id' => $this->admin_id,

        ]);

        $query->andWhere(['!=', 'alert_captchas.status', AlertCaptcha::STATUS_SOLVED]);
        $query->andFilterWhere(['like', 'iq.url', $this->url]);
        $query->andFilterWhere(['like', 'iq.processing_ip', $this->processing_ip]);

        if ($this->created_at) {
            $query->andFilterWhere(['between', 'alert_captchas.created_at', strtotime($this->datetime_min), strtotime($this->iq_datetime_max) + 24 * 3600 - 1]);
        }

        if ($this->updated_at) {
            $query->andFilterWhere(['between', 'alert_captchas.updated_at', strtotime($this->update_datetime_min), strtotime($this->update_datetime_max) + 24 * 3600 - 1]);
        }

        return $dataProvider;
    }
}
