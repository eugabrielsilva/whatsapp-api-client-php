<?php

namespace WhatsAppPHP;

class Util
{
    /**
     * @return string
     */
    public static function snakeToCamelCase(string $name)
    {
        $words = explode('_', $name);
        $camelCase = array_shift($words);
        foreach ($words as $word) {
            $camelCase .= ucfirst(strtolower($word));
        }
        return $camelCase;
    }

    /**
     * @return string
     */
    public static function formatNumber(string $number)
    {
        return preg_replace('/[^0-9]/', '', $number);
    }
}
