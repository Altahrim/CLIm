<?php
namespace Clim\Widget;

use Clim\Helper\Str;
use Clim\Widget;

/**
 * Draw a table
 * @see http://www.amp-what.com/unicode/search/box%20drawing for more characters
 */
class Table extends Widget
{
    const ALIGN_LEFT = 1;
    const ALIGN_RIGHT = 2;
    const ALIGN_CENTER = 3;
    const DISP_FRAME = 1;
    const DISP_ROWS = 2;
    const DISP_COLS = 4;
    private $innerMargin = 2;
    private $rowId;
    /**
     * Data
     *
     * @var string[][]
     */
    private $data;

    /**
     * Columns
     *
     * @var array
     */
    private $columns;

    /**
     * Rows
     *
     * @var array
     */
    private $rows;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->data = [];
        $this->columns = [];
        $this->rowId = 0;
    }

    /**
     * Add some data to current table
     * @param array $data
     * @return $this
     */
    public function addData(array $data = [])
    {
        foreach ($data as $row) {
            $rowId = ++$this->rowId;
            $this->data[$rowId] = [];
            foreach ($row as $colId => $str) {
                $this->makeColumn($colId);
                $col = &$this->columns[$colId];
                $strList = is_array($str) ? $str : explode("\n", $str);
                if (count($strList) > 1) {
                    $this->makeRow($rowId);
                    $this->rows[$rowId]['minHeight'] = max(
                        $this->rows[$rowId]['minHeight'],
                        count($strList)
                    );
                }
                $len = max(array_map(function ($str) { return Str::len($str, true); }, $strList));
                $col['minWidth'] = max($col['minWidth'], $len);
                $this->data[$rowId][$colId] = [
                    'len' => $len,
                    'str' => $strList
                ];
            }
            unset($col);
        }

        return $this;
    }

    /**
     * @param $colId
     */
    private function makeColumn($colId)
    {
        if (!isset($this->columns[$colId])) {
            $this->columns[$colId] = [
                'minWidth' => 0,
                'align' => self::ALIGN_LEFT
            ];
        }
    }

    /**
     * @param $rowId
     */
    private function makeRow($rowId)
    {
        if (!isset($this->rows[$rowId])) {
            $this->rows[$rowId] = [
                'minHeight' => 1
            ];
        }
    }

    /**
     * Draw configured table
     * @param int $flags
     * @return $this
     */
    public function draw($flags = self::DISP_FRAME | self::DISP_COLS | self::DISP_ROWS)
    {
        $this->out->reset();
        $showFrame = $flags & self::DISP_FRAME;
        $showCols = $flags & self::DISP_COLS;
        $showRows = $flags & self::DISP_ROWS;
        $firstRow = true;
        $nbCols = count($this->columns);
        foreach ($this->data as $rowId => $row) {
            // Display vertical lines
            if ($showFrame && $firstRow || $showRows && !$firstRow) {
                if ($firstRow) {
                    $lineBegin = '┏';
                    $lineEnd = '┓';
                } elseif ($showFrame) {
                    $lineBegin = '┠';
                    $lineEnd = '┨';
                } else {
                    $lineBegin = $lineEnd = '';
                }
                echo $lineBegin;
                $firstCol = true;
                $colId = 0;
                foreach ($this->columns as $col) {
                    $lastCol = ++$colId === $nbCols;
                    $sep = '';
                    $margin = $this->innerMargin;
                    if ($showCols) {
                        if (($firstCol && $showFrame) || (!$firstCol && !$lastCol)) {
                            $margin *= 2;
                        }
                        $sep = $firstCol ? '' : ($firstRow ? '┯' : '┼');
                    }
                    if ($lastCol && !$showFrame) {
                        $margin = 0;
                    }
                    echo $sep, str_repeat(($firstRow ? '━' : '─'), $col['minWidth'] + $margin);
                    $firstCol = false;
                }
                if ($showCols) {
                    echo str_repeat($firstRow ? '━' : '─', $this->innerMargin);
                }
                echo $lineEnd, "\n";
            }

            // Display data
            $minHeight = isset($this->rows[$rowId]) ? $this->rows[$rowId]['minHeight'] : 1;
            for ($i = 1; $i <= $minHeight; ++$i) {
                $firstCol = true;
                $colId = 0;
                foreach ($this->columns as $colName => $col) {
                    $lastCol = ++$colId === $nbCols;
                    $margin = str_repeat(' ', $this->innerMargin);
                    if ($firstCol && $showFrame) {
                        echo '┃', $margin;
                    } elseif (!$firstCol && $showCols) {
                        echo '│', $margin;
                    }

                    $data = isset($row[$colName]) ? $row[$colName] : false;
                    $str = $data && isset($data['str'][$i-1]) ? $data['str'][$i-1] : '';
                    if (!empty($str)) {
                        $padLen = $col['minWidth'] - Str::len($str, true);
                        if ($padLen) {
                            if ($col['align'] === self::ALIGN_CENTER) {
                                $padLen /= 2;
                                echo str_repeat(' ', ceil($padLen));
                            } elseif ($col['align'] === self::ALIGN_RIGHT) {
                                echo str_repeat(' ', $padLen);
                            }
                        }
                        echo $str;
                        if ($padLen) {
                            if ($col['align'] === self::ALIGN_CENTER) {
                                echo str_repeat(' ', floor($padLen));
                            } elseif ($col['align'] === self::ALIGN_LEFT) {
                                echo str_repeat(' ', $padLen);
                            }
                        }
                    } else {
                        echo str_repeat(' ', $col['minWidth']);
                    }

                    if ($showFrame || !$lastCol) {
                        echo $margin;
                    }
                    $firstCol = $firstRow = false;
                }
                if ($showFrame) {
                    echo '┃';
                }
                echo "\n";
            }
        }

        // Display frame bottom
        if ($showFrame) {
            $firstCol = true;
            foreach ($this->columns as $colId => $col) {
                $margin = $showCols ? 2 * $this->innerMargin : $this->innerMargin;
                echo($firstCol ? '┗' : ($showCols ? '┷' : '')), str_repeat('━', $col['minWidth'] + $margin);
                $firstCol = false;
            }
            echo($showCols ? '' : str_repeat('━', $this->innerMargin)), '┛', "\n";
        }

        return $this;
    }
}
