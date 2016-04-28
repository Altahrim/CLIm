<?php
namespace CLIm\Helpers;

/**
 * Prompt for answers from user
 * Note: it seems impossible to include escapes characters in readline prompt. So, impossible to have a different
 * color for prompt and for answer (and same for completions)
 */
class Prompt
{
    private static $prompt = ['text' => '# ', 'color' => 37, 'bgColor' => null, 'flags' => null];

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
     * @param $question
     * @return mixed
     * @todo Support multiline answers
     */
    public static function ask($question)
    {
        $out = \CLIm::getInstance()
            ->bell()
            ->style(Style::BOLD)
            ->writeLn($question, \CLIm::VERB_QUIET)
            ->style(Style::BOLD, false)
            ->lf();
        self::colorPrompt();
        $answer = readline(self::$prompt['text']);
        $out->reset();

        return $answer;
    }

    /**
     * Display a question and some answers so the user can select one
     * Currently handle up to 36 options
     * @param string $question
     * @param array $opts
     * @return array [Selected key, Selected value]
     */
    public static function select($question, array $opts)
    {
        $out = \CLIm::getInstance();
        $out
            ->bell()
            ->writeLn($question, \CLIm::VERB_QUIET);
        $i = 0;
        $answers = [];
        $out->verbosity(\CLIm::VERB_QUIET, $oldVerb);
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
        return self::readChar(function ($c) use ($out, $answers, $opts, $oldVerb) {
            if (isset($answers[$c])) {
                $out->writeLn($c)->reset();
                $out->verbosity($oldVerb);
                return [$answers[$c], $opts[$answers[$c]]];
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
    }
}
