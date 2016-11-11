<?php
namespace Clim\Widget\Question;

use Clim\Helper\Cursor;
use Clim\Widget\Question;

/**
 * Prompt for an answer without visual return
 */
class Hidden extends Question
{
    protected $readFunc = 'readHidden';

    private $buffer;
    private $prompting;

    private $showStars = true;

    /**
     * Read a string with output hidden
     * @return string
     */
    protected function readHidden()
    {
        $this->prompting = true;
        $this->buffer = '';
        $line = Cursor::getPos()[0];
        readline_callback_handler_install(' ', [$this, 'lineReady']);
        while ($this->prompting) {
            $read = [STDIN];
            $void = null;
            $n = stream_select($read, $void, $void, null);
            if ($n) {
                readline_callback_read_char();
                if ($this->prompting) {
                    Cursor::move($line, 3);
                    $this->out->esc('0K');
                    if ($this->showStars) {
                        $buffer = readline_info('line_buffer');
                        $this->out->write("\x07" . str_repeat('•', mb_strlen($buffer)));
                    }
                }
            }
        }
        readline_callback_handler_remove();

        return $this->buffer;
    }

    /**
     * Method called by ReadLine when a line is ready
     * @param string $line
     */
    protected function lineReady($line)
    {
        $this->prompting = false;
        $this->buffer = $line;
    }
}