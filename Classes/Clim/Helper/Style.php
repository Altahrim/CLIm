<?php
namespace Clim\Helper;

/**
 * Style
 * Alter text rendition
 *
 * Please note that some of theses styles may not be rendered by your terminal
 *
 * @see https://en.wikipedia.org/wiki/ANSI_escape_code#graphics
 */
class Style
{
    const BOLD      = 1; // or increased intensity
    const DIM       = 2;
    const ITALIC    = 3;
    const UNDERLINE = 4;
    const BLINK     = 5;
    const NEGATIVE  = 7;
    const HIDDEN    = 8;
    const STRIKE    = 9;


    /**
     * Format style into
     * @param int $styles
     * @param bool $add
     * @return mixed
     */
    public static function format($styles, $add = true)
    {
        $cmd = [];
        if (null !== $styles) {
            for ($i = 0; $i <= 7; ++$i) {
                $bit = pow(2, $i);
                if (0 !== ($bit & $styles)) {
                    if (!$add) {
                        $bit += 20;
                    }
                    $cmd[] = $bit;
                }
            }
        }

        return implode(';', $cmd) . 'm';
    }
}
