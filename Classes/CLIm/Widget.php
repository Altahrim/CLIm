<?php
namespace CLIm;

abstract class Widget
{
    /**
     * Console
     * @var \Console
     */
    protected $out;

    public function __construct()
    {
        $class     = __NAMESPACE__;
        $this->out = $class::getInstance();
    }
}
