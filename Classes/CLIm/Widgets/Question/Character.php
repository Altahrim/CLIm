<?php
namespace CLIm\Widgets\Question;

use CLIm\Widgets\Question;

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

    protected function isValidAnswer($char)
    {
        if (empty($this->validChars)) {
            return true;
        }

        return false !== mb_strpos($this->validChars, $char);
    }
}