<?php
namespace Clim\Helper;

/**
 * Cursor helper
 */
class Cursor
{
    public static function savePos()
    {
        \Clim::getInstance()->esc('s');
    }

    public static function restore()
    {
        \Clim::getInstance()->esc('u');
    }/** @noinspection PhpInconsistentReturnPointsInspection */

    /**
     * Return cursor position
     * Format: [0 => row, 1 => column]
     * @return int[]
     */
    public static function getPos()
    {
        $out = \Clim::getInstance();
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
                    case \Clim::ESC:
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
     */
    public static function move($x = 1, $y = 1)
    {
        \Clim::getInstance()->esc($x . ';' . $y . 'H');
    }

    /**
     * @return \Clim|string
     */
    public static function hide()
    {
        return \Clim::getInstance()->esc('?25l');
    }

    /**
     * @return \Clim|string
     */
    public static function show()
    {
        return \Clim::getInstance()->esc('?25h');
    }
}
