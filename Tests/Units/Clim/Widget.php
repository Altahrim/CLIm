<?php
namespace Tests\Units\Clim;

use \mageekguy\atoum\test;

/**
 * Tests for Clim\Widget
 */
class Widget extends test
{
    public function testInit() {
        $c = new static();

        $this->object($c);
    }
}