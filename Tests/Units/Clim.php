<?php
namespace Tests\Units;

use \mageekguy\atoum\test;

/**
 * Tests for CLIm
 */
class Clim extends test
{
    public function testInit() {
        $c = \Clim::getInstance();

        $this->object($c)->isInstanceOf('\Clim');
    }
}