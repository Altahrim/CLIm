<?php
namespace CLIm\Widgets\Question;

use CLIm\Helpers\Cursor;
use CLIm\Helpers\Style;
use CLIm\Widgets\Question;

class Select extends Question
{
    /**
     * Select options
     * @var string[]
     */
    private $choices = [];
    protected $readFunc = 'readChar';


    /**
     * Display a question and some answers so the user can select one
     * Currently handle up to 36 options
     * @return
     */
    /*
    public function ask()
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
            return $ret;        }

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
    */

    /**
     * Add one or more choices to the select
     * @param string[] $label
     * @return $this
     */
    public function addChoice(... $label)
    {
        foreach ($label as $l) {
            $this->choices[] = $l;
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function drawQuestion()
    {
        parent::drawQuestion();
        $this->drawChoices();
    }

    protected function drawChoices()
    {
        foreach ($this->choices as $k => $choice) {
            $this->out
                ->style(Style::BOLD)
                ->color(250)
                ->write('%2s. ', base_convert($k + 1, 10, 36))
                ->reset()
                ->writeLn($choice);
        }
    }

    protected function isValidAnswer($str)
    {
        return array_key_exists($this->raw2Index($str), $this->choices);
    }

    protected function prepareAnswer($str)
    {
        return (string) $this->choices[$this->raw2Index($str)];
    }

    protected function raw2Index($raw)
    {
        return base_convert($raw, 36, 10) - 1;
    }
}