<?php
namespace CLIm\Widgets;

use CLIm\Helpers\Str;
use CLIm\Widget;

/**
 * Manage progress bar
 * @todo Fix blinking
 * @todo Redraw only required?
 */
class ProgressBar extends Widget
{
    private $current;
    private $step;
    private $target;
    private $completed;

    private $type = self::TYPE_NORMAL;


    private $block = '█';
    private $previousDisplay;

    CONST TYPE_NONE    = 0;
    CONST TYPE_PERCENT = 1;
    CONST TYPE_NORMAL  = 2;

    public function init($target)
    {
        // TODO Check positive
        $this->target = (int) $target;
        $this->current = 0;
        $this->completed = false;
        $this->previousDisplay = false;
        $this->draw();
    }

    public function setStep($step)
    {
        // TODO Check positive
        $this->step = (int) $step;
    }

    public function nextStep()
    {
        if ($this->completed) {
            return true;
        }

        $this->current += $this->step;
        if ($this->current >= $this->target) {
            $this->current = $this->target;
            $this->completed = true;
        }
        $this->draw();
        return $this->completed;
    }

    private function draw()
    {
        $availableWidth = $this->out->getCols();
        $progress = $this->getProgress();
        $width = $availableWidth - Str::len($progress, true);

        if ($this->previousDisplay) {
            $this->out->clearLine();
        }

        if ($this->completed) {
            $this->out->color(28);
            echo str_repeat($this->block, $width);
            $this->out->reset();
        } else {
            $len = floor($width * ($this->current / $this->target));
            $this->out->color('#333333');
            echo str_repeat($this->block, $len), str_repeat('▁', $width - $len);
            $this->out->reset();
        }
        echo $progress;
        $this->previousDisplay = true;
        if ($this->completed) {
            $this->out->lf();
        }
    }

    private function getProgress()
    {
        switch ($this->type) {
            case self::TYPE_NONE:
                return '';
            case self::TYPE_PERCENT:
                return sprintf(' [%3d%%]', ($this->current/$this->target) * 100);
            case self::TYPE_NORMAL:
                $len = strlen($this->target);
                return sprintf(' [%' . $len . 'd/%' . $len . 'd]', $this->current, $this->target);
        }

        return false;
    }
}
