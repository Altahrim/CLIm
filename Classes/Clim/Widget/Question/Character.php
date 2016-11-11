<?php
namespace Clim\Widget\Question;

use Clim\Widget\Question;

/**
 * Prompt for a single character
 */
class Character extends Question
{
    protected $readFunc = 'readChar';
    protected $validChars;

    /**
     * Set a list of allowed characters
     * @param $chars
     * @return $this
     */
    public function setValidChars($chars)
    {
        $this->validChars = (string) $chars;
        return $this;
    }

    /**
     * @param $char
     * @return bool
     */
    protected function isValidAnswer($char)
    {
        if (empty($this->validChars)) {
            return true;
        }

        return false !== mb_strpos($this->validChars, $char);
    }
}