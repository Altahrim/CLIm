<?php
namespace Clim;

/**
 * Base for widgets
 */
abstract class Widget
{
    /**
     * Console
     * @var \Clim
     */
    protected $out;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->out = \Clim::getInstance();
    }
}
