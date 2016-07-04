<?php
namespace CLIm\Helpers;

/**
 * Handle colors
 */
class Colors
{
    private $palette = self::PALETTE_256;

    const PALETTE_NONE = 0;
    const PALETTE_8    = 8;
    //const PALETTE_16   = 16;
    //const PALETTE_88   = 88;
    const PALETTE_256  = 256;

    /**
     * Basic colors
     */
    const BLACK   = 0;
    const RED     = 1;
    const GREEN   = 2;
    const YELLOW  = 3;
    const BLUE    = 4;
    const MAGENTA = 5;
    const CYAN    = 6;
    const GREY    = 7;

    /**
     * Constructor
     * @param int $palette
     */
    public function __construct($palette = null)
    {
        $this->palette = null === $palette ? $this->detectPalette() : (int) $palette;
    }

    /**
     * Format a color
     * @param int|string $color
     * @param bool $isBackground
     * @return string
     */
    public function format($color, $isBackground = false)
    {
        $this->palette = self::PALETTE_256;
        if ($color[0] === '#') {
            $color = $this->rgbTo256Colors($color);
        }

        switch ($this->palette) {
            default:
            case self::PALETTE_NONE:
                return false;
            case self::PALETTE_8:
                $color = $this->reducePalette($color);
                $base  = $isBackground ? 40 : 30;
                return $base + $color . 'm';
            case self::PALETTE_256:
                $prefix = $isBackground ? '48' : '38';
                return $prefix . ';5;' . ($color % 256) . 'm';
        }
    }

    private function isSimilarTo($a, $b, $zone) {
        return abs($a - $b) <= $zone;
    }

    private function rgbTo256Colors($color) {
        if (preg_match('/#([0-9A-F]{2})([0-9A-F]{2})([0-9A-F]{2})/Ai', $color, $matches)) {
            list(, $red, $green, $blue) = $matches;
            $red   = hexdec($red);
            $green = hexdec($green);
            $blue  = hexdec($blue);
            $zone  = 10;

            if ($this->isSimilarTo($red, $green, $zone) && $this->isSimilarTo($red, $blue, $zone) && $this->isSimilarTo($blue, $green, $zone)) {
                $avg = ($red + $green + $blue) / 3;
                $color = round($avg / (256 / 24));
                return 0xE8 + $color;
            }

            $ratio = 256 / 6;
            $red   = round($red / $ratio);
            $green = round($green / $ratio);
            $blue  = round($blue / $ratio);

            return 16 + 36 * $red + 6 * $green + $blue;
        }

        throw new \Exception($color . ' n\'est pas une couleur valide');
    }

    private function detectPalette()
    {
        $nbColors = \CLIm::getInstance()->getColors();
        $modes = [self::PALETTE_8, self::PALETTE_256];
        $palette = self::PALETTE_NONE;
        foreach ($modes as $mode) {
            if ($nbColors >= $mode) {
                $palette = $mode;
                if ($nbColors === $mode) {
                    break;
                }
            }
        }

        return $palette;
    }

    public function getPalette()
    {
        return $this->palette;
    }

    private function reducePalette($color)
    {
        if (0xFF < $color || 0 > $color) {
            throw new \Exception('Invalid color ' . $color);
        }

        /* 0x00-0x07:  standard colors (as in ESC [ 30–37 m) */
        if ($color <= 0x07) {
            return $color;
        }

        /* 0x08-0x0F:  high intensity colors (as in ESC [ 90–97 m) */
        if ($color <= 0x0F) {
            return $color - 8;
        }

        /* 0x10-0xE7:  6 × 6 × 6 = 216 colors: 16 + 36 × r + 6 × g + b (0 ≤ r, g, b ≤ 5) */
        if ($color <= 0xE7) {
            $color    -= 16;
            $blue      = $color % 6;
            $green     = floor($color / 6) % 6;
            $red       = (int) floor($color / 36);

            if ($blue === $green && $blue === $red) {
                return $blue > 2 ? self::GREY : self::BLACK;
            }

            $min    = min($red, $green, $blue);
            $dRed   = $red - $min;
            $dGreen = $green - $min;
            $dBlue  = $blue - $min;
            $minDiff = 1;

            if (!$dRed) {
                if (!$dGreen || $dBlue > $dGreen + $minDiff) {
                    return self::BLUE;
                }
                if (!$dBlue || $dGreen > $dBlue + $minDiff) {
                    return self::GREEN;
                }
                return self::CYAN;
            }
            if (!$dGreen) {
                if (!$dBlue || $dRed > $dBlue + $minDiff) {
                    return self::RED;
                }
                return self::MAGENTA;
            }
            if (!$dBlue) {
                return self::YELLOW;
            }

            return max($red, $green, $blue) > 2 ? self::GREY : self::BLACK;
        }

        /* 0xE8-0xFF:  greyscale from black to white in 24 steps */
        return $color <= 0xF3 ? self::BLACK : self::GREY;
    }
}

