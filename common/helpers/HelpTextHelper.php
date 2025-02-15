<?php


namespace common\helpers;


use common\models\HelpTexts;

class HelpTextHelper
{
    private static $helpTexts;

    public static function getHelpText(string $key, string $field = 'title', array $replacements = []): ?string
    {
        if (!isset(self::$helpTexts)) {
            self::$helpTexts = HelpTexts::find()->asArray()->indexBy('key')->all();
        }

        $text = self::$helpTexts[$key][$field] ?? null;

		if ($replacements) {
			$text = str_replace(array_keys($replacements), array_values($replacements), $text);
		}

		return $text;
    }

}