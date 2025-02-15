<?php


namespace backend\helpers;


use common\models\Currency;
use Yii;

class CurrencyHelper
{
    const ACCESS_KEY = 'f49b3b74c7479677eb4c0a121bfecaae';
    const LIVE_URL = "http://api.currencylayer.com/live?access_key=" . self::ACCESS_KEY . "&format=1";
    const LIST_URL = "http://api.currencylayer.com/list?access_key=" . self::ACCESS_KEY . "&format=1";

    public static function getCurrencyRates()
    {
        $currency_rates = Yii::$app->cache->get('currency_rate');
        if (empty($currency_rates['quotes'])) {
            $currency_rates = json_decode(
                file_get_contents(
                    self::LIVE_URL
                ), true);
            if ($currency_rates) {
                Yii::$app->cache->set('currency_rate', $currency_rates, 24 * 3600);
            }
        }


        return $currency_rates['quotes'] ?? [];
    }


    public static function getCurrencyList()
    {
        $currency_list = Yii::$app->cache->get('currency_list');
        if (empty($currency_list['currencies'])) {
            $currency_list = json_decode(
                file_get_contents(
                    self::LIST_URL
                ), true);
            if ($currency_list) {
                Yii::$app->cache->set('currency_list', $currency_list, 24 * 3600);
            }
        }


        return $currency_list['currencies'] ?? [];
    }

    public static function convertToUSD($from, $amount)
    {
        $currencies = strtoupper(Currency::DEFAULT_CURRENCY) . strtoupper($from);
        $currencyRates = self::getCurrencyRates();
        if (array_key_exists($currencies, $currencyRates)) {
            $rate = $currencyRates[$currencies];
            return $amount / $rate;
        }
    }

    public static function convert($from, $to)
    {
        $convertedFrom = $from !== 'USD' ? CurrencyHelper::convertToUSD($from, 1) : 1;
        $convertedTo = $to !== 'USD' ? CurrencyHelper::convertToUSD($to, 1) : 1;

        return $convertedFrom / $convertedTo;
    }
}
