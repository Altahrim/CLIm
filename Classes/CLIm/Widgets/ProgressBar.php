<?php
namespace CLIm\Widgets;

use CLIm\Helpers\Cursor;
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
    private $pos;

    private $type = self::TYPE_NORMAL;
    private $element = '';

    private $block = '█';
    private $previousDisplay;

    CONST TYPE_NONE    = 0;
    CONST TYPE_PERCENT = 1;
    CONST TYPE_NORMAL  = 2;

    public function init($target, $element = '')
    {
        // TODO Check positive
        $this->target = (int) $target;
        $this->current = 0;
        $this->completed = false;
        $this->previousDisplay = false;
        $this->element = $element;
        $this->draw();
    }

    public function setCurrent($current, $element = '')
    {
        // TODO Check positive
        $this->current = (int) $current;
        if ($this->current >= $this->target) {
            $this->current = $this->target;
            $this->completed = true;
        }
        $this->element = $element;
        $this->draw();
        return $this->completed;
    }

    public function setStep($step = 1) {
        $this->step = (int) $step;
    }

    public function nextStep($element = '')
    {
        return $this->setCurrent($this->current + $this->step, $element);
    }

    private function draw()
    {
        $availableWidth = $this->out->getCols();
        $progress = $this->getProgress();
        $width = $availableWidth - Str::len($progress, true);

        if ($this->previousDisplay) {
            Cursor::move($this->pos[0]);
            $this->out->esc('[0J');
        } else {
            $this->pos = Cursor::getPos();
            // Save some space (hack when reaching end of screen)
            $this->out->lf(3);
            $newPos = Cursor::getPos();
            $this->pos[0] = max($newPos[0] - 3, $this->pos[0] - 3);
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
        $this->out->write($progress);
        if ($this->element) {
            $this->out
                ->lf()
                ->color(25)
                ->write(':: ')
                ->reset()
                ->write($this->element)
                ->color(25)
                ->write(' ::')
                ->reset();
        }
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
