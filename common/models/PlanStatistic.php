<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%plan_statistics}}".
 *
 * @property int $id
 * @property int|null $plan_id
 * @property int|null $total
 * @property int|null $date
 *
 * @property Plan $plan
 */
class PlanStatistic extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plan_statistics}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['plan_id', 'total', 'date'], 'integer'],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Plan::class, 'targetAttribute' => ['plan_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'plan_id' => 'Plan',
            'total' => 'Total',
            'date' => 'Date',
        ];
    }

    /**
     * Gets query for [[Plan]].
     *
     * @return ActiveQuery
     */
    public function getPlan(): ActiveQuery
    {
        return $this->hasOne(Plan::class, ['id' => 'plan_id']);
    }

	public static function getStatistics(int $startDate, int $endDate): array
	{
		$statistics = self::find()
			->with('plan')
			->where(['>=', 'date', $startDate])
			->andWhere(['<=', 'date', $endDate])
			->groupBy(['plan_id', 'date'])
			->all();

		$result = [];
		foreach ($statistics as $statistic) {
			/* @var PlanStatistic $statistic */
			if (!isset($result[$statistic->plan_id])) {
				$result[$statistic->plan_id] = [
					'name' => $statistic->plan->name,
					'color' => $statistic->plan->color
				];
			}
			$result[$statistic['plan_id']]['data'][] = $statistic['total'];
		}

		return $result;
	}

	public static function getStatisticsForTable(int $startDate, int $endDate): array
	{
		$statistics = self::find()
			->with('plan')
			->where(['>=', 'date', $startDate])
			->andWhere(['<=', 'date', $endDate])
			->groupBy(['plan_id', 'date'])
			->orderBy('date DESC')
			->all();
		
		$result = [];
		foreach ($statistics as $statistic) {
			/* @var PlanStatistic $statistic */
			$result[$statistic->date]['date'] = date('Y-m-d', $statistic->date);
			if (!isset($result[$statistic->date][$statistic->plan->name])) {
				$result[$statistic->date][$statistic->plan->name] = $statistic->total;
			}
		}

		return $result;
	}
}
