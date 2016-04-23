<?php
namespace CLIm\Widgets;

use CLIm\Widget;

/**
 * Draw an unordered list
 */
class BulletList extends Widget
{
    /**
     * Bullets used for each level
     * @var string[]
     */
    private $listLevels = [1 => '•', '◦', '▪', '▫', '▸', '▹'];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Draw the list
     * @param array $data
     */
    public function draw(array $data)
    {
        $this->out->reset();
        reset($data);
        $ptr = &$data;
        $pointers = [];
        $lvl = 1;
        while (!empty($ptr) || (end($pointers) && $lvl = (int) key($pointers) && $ptr = array_pop($pointers))) {
            $key = key($ptr);
            $this->out->write(str_repeat(' ', $lvl) . $this->listLevels[$lvl] . ' ');
            $item = array_shift($ptr);
            if (is_array($item)) {
                $this->out->write($key);
                if (!empty($ptr)) {
                    $pointers[$lvl] = $ptr;
                }
                $ptr = $item;
                ++$lvl;
            } else {
                $this->out->write($item);
            }
            $this->out->lf();
        }
    }
}
