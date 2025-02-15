<?php


namespace frontend\models;


use common\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\validators\CompareValidator;

/**
 * User model
 *
 * @property integer $id
 * @property integer $user_id
 * @property double $price_min
 * @property double $price_max
 * @property boolean $price_markup
 * @property boolean $compare_at_price_markup
 * @property double $price_by_percent
 * @property double $price_by_amount
 * @property double $compare_at_price_by_amount
 * @property double $compare_at_price_by_percent
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property User $user
 */
class ProductPricingRule extends ActiveRecord
{

  const MAX_RULES_COUNT = 100;

  /**
   * {@inheritdoc}
   */
  public static function tableName(): string
  {
    return '{{%product_pricing_rules}}';
  }


  /**
   * @return string[]
   */
  public function behaviors(): array
  {
    return [
      TimestampBehavior::class,
    ];
  }

  /**
   * @return array
   */
  public function rules(): array
  {
    return [
      [['price_min', 'price_max', 'price_by_percent', 'price_by_amount', 'compare_at_price_by_amount', 'compare_at_price_by_percent'], 'double'],
      [['price_min', 'price_max', 'price_by_percent', 'price_by_amount', 'compare_at_price_by_amount', 'compare_at_price_by_percent', 'compare_at_price_markup', 'price_markup', 'user_id'], 'required'],
      [['price_markup', 'compare_at_price_markup'], 'integer'],
      [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
      [['price_min', 'price_max'], 'checkExistingInRange'],
      ['price_max', CompareValidator::class, 'compareAttribute' => 'price_min', 'operator' => '>']
    ];
  }

  /**
   * @return string[]
   */
  public function attributeLabels(): array
  {
    return [
      'id' => 'ID',
      'user_id' => 'User ID',
      'price_min' => 'Price Min',
      'price_max' => 'Price Max',
      'price_markup' => 'Price Markup',
      'compare_at_price_markup' => 'Compare Price Markup',
      'price_by_percent' => 'Price By Percent',
      'price_by_amount' => 'Price By Amount',
      'compare_at_price_by_amount' => 'Compare Price By Amount',
      'compare_at_price_by_percent' => 'Compare Price By Amount',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUser(): ActiveQuery
  {
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }

  /**
   * @param $attribute
   * @return bool
   */
  public  function checkExistingInRange($attribute)
  {

     $query = self::find()
      ->where(
        [
          'and',
          [
            'or',
            [
              'and',
              ['<=', 'price_min', $this->price_min],
              ['>=', 'price_max', $this->price_min],
            ],
            [
              'and',
              ['<=', 'price_min', $this->price_max],
              ['>=', 'price_max', $this->price_max],
            ],
            [
              'and',
              ['>=', 'price_min', $this->price_min],
              ['<=', 'price_max', $this->price_max],
            ],
          ],
          ['=', 'user_id', $this->user_id]
        ]
        );

     if ($this->id) {
       $query->andWhere(['!=', 'id', $this->id]);
     }

    if ($query->count() > 0) {
      $this->addError($attribute, "You already have price rule in min price-$this->price_min max price-$this->price_max range");
    }

    return true;
  }

}