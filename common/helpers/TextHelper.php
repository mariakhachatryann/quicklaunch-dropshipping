<?php


namespace common\helpers;


class TextHelper
{
    public static function decodeText(string $text, int $count = 0): string
    {
        if ($text !== urldecode($text) && $count < 5) {
            return self::decodeText(urldecode($text), ++$count);
        }

        return $text;

    }
}