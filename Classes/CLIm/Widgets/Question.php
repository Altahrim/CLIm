<?php
namespace CLIm\Widgets;

use CLIm\Helpers\Answers;
use CLIm\Helpers\Cursor;
use CLIm\Helpers\Style;
use CLIm\Widget;

/**
 * Prompt user for an answer
 * Note : PHP readline don't support
 */
class Question extends Widget
{
    /**
     * Question text
     * @var string
     */
    private $text;

    /**
     * Question ID
     * Allow automatic answers for current question
     * @var string|null
     */
    private $id = null;

    /**
     * Default value
     * @var string
     */
    protected $default = '';

    /**
     * Previously saved answer
     * If a question is asked twice, user is only prompted once
     * @var string
     */
    private $answer = null;

    protected $readFunc = 'readStr';

    /**
     * Displays the question and wait for an answer
     * If the question was already asked, only returns the answer
     * @return string
     */
    public function getAnswer()
    {
        if (null !== $this->answer) {
            return $this->answer;
        }

        // Handle automatic
        $answer = $this->getAutoAnswer();
        $hasAnswser = null !== $answer;

        // If we need an answer, script verbosity should be at least normal so user can see the question
        if (!$hasAnswser) {
            $this->out->setScriptVerbosity(max(\CLIm::VERB_NORMAL, $this->out->getScriptVerbosity()), $oldVerb);
            $this->out->bell();
        }

        $this->drawQuestion();

        if ($hasAnswser) {
            $answer = $this->prepareAnswer($answer);
            $this->out->writeLn($answer);
        } else {
            while (true) {
                $raw = $this->readAnswer();
                if ('' === $raw) {
                    $raw = $this->default;
                }

                if ($this->isValidAnswer($raw)) {
                    $answer = $this->prepareAnswer($raw);
                    break;
                } else {
                    // TODO Display invalid
                    continue;
                }
            }

            if ($this->id) {
                Answers::setAnswer($this->id, $answer);
            }
            $this->out->setScriptVerbosity($oldVerb);
        }

        return $this->answer = $answer;
    }

    /**
     * Display the question
     */
    protected function drawQuestion()
    {
        $this->out
            ->style(Style::BOLD)
            ->write($this->text)
            ->style(Style::BOLD, false);
        if ($this->id && $this->out->getScriptVerbosity() >= \CLIm::VERB_DEBUG) {
            $this->out->debug(' [' . $this->id . ']');
        } else {
            $this->out->lf();
        }
    }

    protected function displayPrompt()
    {
        $this->out
            ->style(Style::BOLD)
            ->color(33)
            ->write('>')
            ->reset();
    }

    protected function readAnswer()
    {
        $this->displayPrompt();
        Cursor::show();
        // Go to empty dir to avoid ReadLine built-in auto-completion
        $dir = getcwd();
        chdir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR .  'EmptyDir');
        $str = call_user_func([$this, $this->readFunc]);
        chdir($dir);
        Cursor::hide();
        return $str;
    }

    /**
     * Read a whole line from input
     * @return string
     */
    protected function readStr()
    {
        readline_completion_function([$this, 'autocomplete']);
        return rtrim(readline(' '), ' ');
    }

    /**
     * @param string $buffer
     * @param int $pos
     * @param int $end
     * @return string[]
     * @todo Handle auto-completion when cursor is in the middle of the string
     */
    protected function autocomplete($buffer, $pos, $end)
    {
        $results = $this->getAutocompletions($buffer, $pos, $end);
        $nbRes = count($results);
        if ($nbRes <= 1) {
            return $results;
        }

        $this->out->lf();
        $this->out->writeLn(implode("\t", $results));
        $this->displayPrompt();
        $this->out->write(' ' . readline_info('line_buffer'));
        return [];
    }

    /**
     * @param string $buffer
     * @param int $pos
     * @param int $end
     * @return string[]
     */
    protected function getAutocompletions($buffer, $pos, $end)
    {
        return [];
    }

    /**
     * Read a single character from input
     * @return string
     * @todo up to 4 characters could be read by this method even if only one is returned
     */
    protected function readChar()
    {
        Cursor::show();
        readline_callback_handler_install(' ', function () {});
        while (true) {
            $read = [STDIN];
            $void = null;
            $n = stream_select($read, $void, $void, 100);
            if ($n) {
                $chars = fread(STDIN, 4);
                $chars = preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', $chars);
                $chars = preg_split('//u', $chars, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($chars as $char) {
                    if (strlen($char) >= 1 && $this->isValidAnswer($char)) {
                        $this->out->writeLn($char);
                        readline_callback_handler_remove();
                        Cursor::hide();
                        return $char;
                    }
                }
            }
        }
    }

    protected function isValidAnswer($str)
    {
        return true;
    }

    protected function prepareAnswer($str)
    {
        return $str;
    }

    /**
     * Set question
     * @param $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Set question ID
     * Allow automation
     * @param string $id
     * @return $this
     */
    public function setId($id = null)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set default answer.
     * @param $default
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    protected function getAutoAnswer()
    {
        if (empty($this->id)) {
            return null;
        }

        $answer = Answers::getAnswer($this->id);
        if (null === $answer) {
            return null;
        }

        return $this->isValidAnswer($answer) ? $answer : null;
    }
}
