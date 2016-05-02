<?php
namespace CLIm\Helpers;

/**
 * Prompt for answers from user
 * Note: it seems impossible to include escapes characters in readline prompt. So, impossible to have a different
 * color for prompt and for answer (and same for completions)
 * @todo Factorization
 */
class Prompt
{
    const ALLOWED_YES = ['1', 'true', 'yes', 'y'];
    const ALLOWED_NO = ['0', 'false', 'no', 'n'];


    /**
     * Pre-defined answers
     * @var string[]
     */
    private static $answers = [];

    /**
     * Enable or disable the recording of anwsers
     * @var bool
     */
    private static $recordEnabled = false;

    /**
     * Prompt configuration
     * @var array
     */
    private static $prompt = ['text' => '# ', 'color' => 37, 'bgColor' => null, 'flags' => null];

    /**
     * Load some pre-defined answers
     * Format : [questionId1 => answer1, questionId2 => answer2…]
     * @param string[] $answers
     * @param bool $keepOld If false, previous answers will be erased
     */
    public static function loadAnswsers(array $answers, $keepOld = false)
    {
        self::$answers = $keepOld ? array_merge(self::$answers) : $answers;
    }

    /**
     * Load pre-defined answers from a JSON file
     * @param $filepath
     * @param bool $keepOld
     * @throws \Exception
     */
    public static function loadAnswersFromFile($filepath, $keepOld = false)
    {
        if (!is_file($filepath)) {
            throw new \Exception('Impossible to load answers: file "' . $filepath . '" not found');
        }
        $json = file_get_contents($filepath);
        if (false === $json) {
            throw new \Exception('Impossible to load answers: file "' . $filepath . '" is unreadable');
        } elseif (empty($json)) {
            throw new \Exception('Impossible to load answers: file "' . $filepath . '" is empty');
        }
        
        $data = json_decode($json, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception('Impossible to load answers: invalid json data (' . json_last_error_msg() . ')');
        }

        self::loadAnswsers($data, $keepOld);
    }

    /**
     * Add (or replace) a single answer
     * @param $questionId
     * @param $answer
     */
    public static function setAnswer($questionId, $answer)
    {
        self::$answers[$questionId] = $answer;
    }

    /**
     * Remove a single answer
     * Useful if an invalid answer was provided
     * @param $questionId
     */
    public static function unsetAnswer($questionId)
    {
        unset(self::$answers[$questionId]);
    }

    /**
     * Enable (or disable) the recording of answers
     * @param bool $activate
     */
    public static function record($activate = true)
    {
        self::$recordEnabled = (bool) $activate;
    }

    /**
     * Save answers to specified file
     * @param $filepath
     * @throws \Exception
     */
    public static function saveAnswersToFile($filepath)
    {
        $json = json_encode(self::$answers, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        if (false === file_put_contents($filepath, $json)) {
            throw new \Exception('Impossible to save answers');
        }
    }

    /**
     * Personalize prompt
     * @param string $prompt
     * @param string|int|null $color
     * @param string|int|null $bgColor
     * @param int|null $flags
     */
    public static function setPrompt($prompt, $color = null, $bgColor = null, $flags = null)
    {
        self::$prompt = [
            'text' => $prompt . ' ',
            'color' => $color,
            'bgColor' => $bgColor,
            'flags' => $flags
        ];
    }

    /**
     * Display prompt
     */
    protected static function displayPrompt()
    {
        self::colorPrompt();
        \CLIm::getInstance()->write(self::$prompt['text']);
    }

    /**
     * Set prompt attributes (color, style)
     */
    protected static function colorPrompt()
    {
        $out = \CLIm::getInstance();
        if (self::$prompt['color']) {
            $out->color(self::$prompt['color']);
        }
        if (self::$prompt['bgColor']) {
            $out->bgColor(self::$prompt['bgColor']);
        }
        if (self::$prompt['flags']) {
            $out->style(self::$prompt['flags']);
        }
    }

    /**
     * Display a question and wait for an answer
     * @param string $question
     * @param string $qid Question ID (for automatic answers)
     * @return mixed
     * @todo Add support for multi-line answers
     */
    public static function ask($question, $qid = null)
    {
        $out = \CLIm::getInstance()
            ->bell()
            ->style(Style::BOLD)
            ->write($question, \CLIm::VERB_QUIET)
            ->style(Style::BOLD, false);
        if ($qid && $out->getScriptVerbosity() >= \CLIm::VERB_DEBUG) {
            $out->debug(' [' . $qid . ']');
        } else {
            $out->lf();
        }
        if (isset(self::$answers[$qid])) {
            $answer = (string) self::$answers[$qid];
            self::displayPrompt();
            $out->writeLn($answer);
        } else {
            self::colorPrompt();
            Cursor::show();
            $answer = readline(self::$prompt['text']);
            Cursor::hide();
        }
        $out->reset();

        if (self::$recordEnabled && $qid) {
            self::$answers[$qid] = $answer;
        }

        return $answer;
    }

    /**
     * Display a question and wait for an answer.
     * The answer is converted to boolean if possible.
     * @param string $question
     * @param bool|null $default If not null, $default will be used as an answer if answer is empty
     * @param string $qid
     * @return bool|null
     */
    public static function askBool($question, $default = null, $qid = null)
    {
        $answer = self::ask($question, $qid);
        while (true) {
            if (strlen($answer) === 0 && null !== $default) {
                return (bool)$default;
            }

            $bool = self::castToBool($answer);
            if (null !== $bool) {
                return $bool;
            }

            self::displayPrompt();
            $answer = readline();
        };
    }

    /**
     * Cast a string to boolean
     * If conversion is not possible, returns null
     * @param $str
     * @return bool|null
     */
    protected static function castToBool($str)
    {
        if (in_array($str, self::ALLOWED_YES, true)) {
            return true;
        }
        if (in_array($str, self::ALLOWED_NO, true)) {
            return false;
        }
        return null;
    }

    /**
     * Hidden prompt. Could be useful for password
     * @param string $question
     * @param bool $showStars
     * @param string $qid
     * @return string
     */
    public static function hidden($question, $showStars = true, $qid = null)
    {
        readline_callback_handler_install('', function () {});
        $out = \CLIm::getInstance();
        Cursor::savePos();
        Cursor::show();
        $out->bell();
        if (strlen($question)) {
            $out->write($question);
        }
        if ($qid && $out->getScriptVerbosity() >= \CLIm::VERB_DEBUG) {
            $out->debug(' [' . $qid . ']');
        } else {
            $out->lf();
        }
        self::displayPrompt();

        if (isset(self::$answers[$qid])) {
            $answer = (string) self::$answers[$qid];
            if ($showStars) {
                $out->writeLn(str_repeat('•', mb_strlen($answer)));
            }
            return $answer;
        }

        $buffer = '';
        $length = 0;
        while (true) {
            $read = [STDIN];
            $void = null;
            $n = stream_select($read, $void, $void, null);
            if ($n && in_array(STDIN, $read)) {
                $char = fread(STDIN, 30);
                switch ($char[0]) {
                    case "\e": // Escape character
                        break;
                    case "\x7f": // Backspace
                        if ($length > 1) {
                            $buffer = substr($buffer, 0, -1);
                            --$length;
                            if ($showStars) {
                                $out->esc('1D')->esc('0K');
                            }
                        }
                        break;
                    case PHP_EOL:
                        $out->lf();
                        break 2;
                    default:
                        $len = mb_strlen($char);
                        $buffer .= $char;
                        $length += $len;
                        if ($showStars) {
                            $out->write(str_repeat('⚫', $len));
                        }
                }
            }
        }
        Cursor::hide();

        if (self::$recordEnabled && $qid) {
            self::$answers[$qid] = $buffer;
        }

        return $buffer;
    }

    /**
     * Display a question and some answers so the user can select one
     * Currently handle up to 36 options
     * @param string $question
     * @param array $opts
     * @param string $qid Question ID (for automatic answers)
     * @return array From $opts array: [Selected key, Selected value]
     */
    public static function select($question, array $opts, $qid = null)
    {
        $out = \CLIm::getInstance();
        $ret = $c = null;
        if (isset(self::$answers[$qid])) {
            $c = self::$answers[$qid];
            $pos = base_convert($c, 36, 10) - 1;
            $copy = $opts;
            if ($pos >= 0 && $ret = array_splice($copy, $pos, 1))  {
                $ret = [key($ret), current($ret)];
            }
        }

        if ($out->getScriptVerbosity() <= \CLIm::VERB_QUIET) {
            return $ret;
        }

        $out->verbosity(\CLIm::VERB_QUIET, $oldVerb);

        // Display question (and question ID if debug)
        $out
            ->bell()
            ->write($question, \CLIm::VERB_QUIET);
        if ($qid && $out->getScriptVerbosity() >= \CLIm::VERB_DEBUG) {
            $out->debug(' [' . $qid . ']');
        } else {
            $out->lf();
        }

        // Display choices
        $i = 0;
        $answers = [];
        foreach ($opts as $k => $v) {
            $id = base_convert(++$i, 10, 36);
            $out
                ->color(245)
                ->write('  %s. ', $id)
                ->reset()
                ->writeLn($v);
            $answers[$id] = $k;
        }

        self::displayPrompt();

        // Handle pre-answer
        if ($c) {
            if ($ret) {
                $out->writeLn($c)->reset()->verbosity($oldVerb);
                return $ret;
            } elseif ($oldVerb >= \CLIm::VERB_DEBUG) {
                $out->error('Invalid answer "' . $c . '" for question "' . $qid . '"');
            }
        }

        // No answer (or invalid one), prompt user
        return self::readChar(function ($c) use ($out, $answers, $opts, $oldVerb, $qid) {
            if (isset($answers[$c])) {
                $out->writeLn($c)->reset()->verbosity($oldVerb);
                $ret = [$answers[$c], $opts[$answers[$c]]];

                if (self::$recordEnabled && $qid) {
                    self::$answers[$qid] = $ret;
                }

                return $ret;
            }

            return false;
        });
    }

    /**
     * Read chars from STDIN
     * For each character read, $cb is called with the char as first parameter.
     * While $cb return false, the function will continue
     * @param callable $cb
     * @return mixed
     */
    public static function readChar(callable $cb)
    {
        readline_callback_handler_install('', function () {});
        while (true) {
            $read = [STDIN];
            $void = null;
            $n = stream_select($read, $void, $void, 100);
            if ($n && in_array(STDIN, $read)) {
                $chars = fread(STDIN, 1024);
                $chars = preg_split('//u', $chars, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($chars as $char) {
                    if (!empty($char) && false !== ($res = $cb($char))) {
                        readline_callback_handler_remove();
                        return $res;
                    }
                }
            }
        }

        return false;
    }
}
