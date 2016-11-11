<?php
namespace Clim\Widget\Question;

/**
 * Prompt for an answer (yes/no)
 */
class YesNo extends Character
{
    const ALLOWED_YES = 'yYoO';
    const ALLOWED_NO = 'nN';

    protected $validChars = self::ALLOWED_NO . self::ALLOWED_YES;

    /**
     * Cast a string to boolean
     * @param $char
     * @return bool
     */
    protected function prepareAnswer($char)
    {
        return false !== strpos(self::ALLOWED_YES, $char);
    }
}