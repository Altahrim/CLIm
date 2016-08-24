<?php
namespace CLIm\Widgets\Question;

class YesNo extends Character
{
    const ALLOWED_YES = 'yYoO';
    const ALLOWED_NO = 'nN';

    private $validChars = self::ALLOWED_NO . self::ALLOWED_YES;

    public function __construct()
    {
        parent::__construct();
        $this->setDefault('y');
    }

    /**
     * Cast a string to boolean
     * @param $char
     * @return bool
     */
    protected function prepareAnswer($char)
    {
        return false !== strpos($char, self::ALLOWED_YES, true);
    }
}