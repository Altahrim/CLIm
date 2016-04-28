<?php
namespace CLIm\Helpers;

/**
 * Cursor helper
 */
class Cursor
{
    public static function savePos()
    {
        \CLim::getInstance()->esc('s');
    }

    public static function restore()
    {
        \CLim::getInstance()->esc('u');
    }

    /**
     * Return cursor position
     * Format: [0 => row, 1 => column]
     * @return int[]
     */
    public static function getPos()
    {
        $out = \CLIm::getInstance();
        $out->esc('6n');

        $row = $col = '';
        $ptr = &$row;
        readline_callback_handler_install('', function () {});
        while (true) {
            $r = [STDIN];
            $w = null;
            $e = null;
            $n = stream_select($r, $w, $e, 100);
            if ($n && in_array(STDIN, $r)) {
                $c = stream_get_contents(STDIN, 1);
                switch ($c) {
                    case \CLIm::ESC:
                    case '[':
                        break;
                    case ';':
                        $ptr = &$col;
                        break;
                    case 'R':
                        return [(int) $row, (int) $col];
                    default:
                        $ptr .= $c;
                }
            }
        }
    }

    /**
     * Move cursor to a new position
     * @param int $x
     * @param int $y
     * @return $this
     */
    public static function move($x = 1, $y = 1)
    {
        return \CLim::getInstance()->esc($x . ';' . $y . 'H');
    }

    public static function hide()
    {
        return \CLim::getInstance()->esc('?25l');
    }

    public static function show()
    {
        return \CLim::getInstance()->esc('?25h');
    }
}
