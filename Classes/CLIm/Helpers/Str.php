<?php
namespace CLIm\Helpers;

/**
 * String helper which takes care of control characters
 */
class Str
{
    /**
     * Default encoding
     */
    const ENCODING = 'UTF-8';

    /**
     * Get string length
     * If $asConsole, length will be calculated as space used in console
     * @param string $string
     * @param bool $asConsole
     * @return mixed
     */
    public static function len($string, $asConsole = false)
    {
        if ($asConsole) {
            $string = self::clean($string);
        }

        return mb_strlen($string, self::ENCODING);
    }

    /**
     * Get part of string
     * @param $string
     * @param int $start
     * @param int|null $length
     * @return string
     */
    public static function sub($string, $start = 0, $length = null)
    {
        return mb_substr($string, $start, $length, self::ENCODING);
    }

    /**
     * Clean a string to remove escape characters
     * @param $string
     * @return string
     */
    public static function clean($string)
    {
        $pcre = '#\033\[\d+(;\d+)*m#u';
        $str  = preg_replace($pcre, '', $string, -1, $count);
        //echo "\n", $pcre, ' Before: ', $string, "\033[0m", ' After: ', $str, "$count\n";
        return $str;
    }
}
