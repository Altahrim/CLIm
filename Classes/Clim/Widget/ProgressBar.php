<?php
namespace Clim\Widget;

use Clim\Helper\Cursor;
use Clim\Helper\Str;
use Clim\Widget;

/**
 * Manage progress bars
 * @todo Fix blinking / Redraw only required?
 */
class ProgressBar extends Widget
{
    private $current;
    private $step;
    private $target;
    private $completed;
    private $pos;
    private $error;

    private $type = self::TYPE_NORMAL;
    private $element = '';

    private $block = '█';
    private $previousDisplay;

    CONST TYPE_NONE    = 0;
    CONST TYPE_PERCENT = 1;
    CONST TYPE_NORMAL  = 2;

    /**
     * Initialization
     * @param $target
     * @param string $element
     */
    public function init($target, $element = '')
    {
        // TODO Check positive
        $this->target = (int) $target;
        $this->current = 0;
        $this->completed = false;
        $this->previousDisplay = false;
        $this->element = $element;
        $this->error = false;
        $this->draw();
    }

    /**
     * Set current advancement
     * @param $current
     * @param string $element
     * @return bool
     */
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

    /**
     * Set step size
     * Useful for nextStep()
     * @param int $step
     */
    public function setStep($step = 1) {
        $this->step = (int) $step;
    }

    /**
     * Go to next step (as configured in setStep)
     * @param string $element
     * @return bool
     */
    public function nextStep($element = '')
    {
        return $this->setCurrent($this->current + $this->step, $element);
    }

    /**
     * (Re)draw the progress bar
     */
    private function draw()
    {
        $availableWidth = $this->out->getCols();
        $progress = $this->getProgress();
        $width = $availableWidth - Str::len($progress, true);

        if ($this->previousDisplay) {
            Cursor::move($this->pos[0]);
            $this->out->esc('0J');
        } else {
            $this->pos = Cursor::getPos();
            // Save some space (hack when reaching end of screen)
            $this->out->lf(3);
            $newPos = Cursor::getPos();
            $this->pos[0] = max($newPos[0] - 3, $this->pos[0] - 3);
            Cursor::move($this->pos[0]);
        }

        if ($this->completed) {
            $this->out->color($this->error ? 52 : 28);
            echo str_repeat($this->block, $width);
            $this->out->reset();
        } else {
            $len = floor($width * ($this->current / $this->target));
            $this->out->color($this->error ? 52 : 30);
            echo str_repeat($this->block, $len), str_repeat('▁', $width - $len);
            $this->out->reset();
        }
        $progress = str_replace('%', '%%', $progress);
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

    /**
     * Format progression widget
     * @return string
     */
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

        return '';
    }

    /**
     * Change progression widget type
     * @param $type
     */
    public function setType($type)
    {
        $this->type = (int) $type;
    }

    /**
     * Mark the progress bar as errored
     */
    public function setError()
    {
        $this->error = true;
        $this->draw();
    }
}
