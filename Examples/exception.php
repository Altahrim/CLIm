#!/usr/bin/php
<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Vendor' .  DIRECTORY_SEPARATOR . 'autoload.php';

$out = Clim::getInstance();
$out->clear();

// You have to register handlers
$out->registerErrorHandlers();

// Then, build a nice application...
/**
 * Class Test
 */
class Test
{
    public function doSomething()
    {
        self::doSomethingNasty(':)', ['This', 'is', 'the', 'end'], 13.37);
    }

    /**
     * @param $a
     * @param array $b
     * @param $c
     * @throws Exception
     */
    protected static function doSomethingNasty($a, array $b, $c)
    {
        throw new \Exception('Wow, that escalated quickly!', 42, new Exception('Another one'));
    }
}

function launchThisMess()
{
    $obj = new Test();
    $obj->doSomething();
}

// ... that will crash
$out->writeLn('At first, it was nice...');
$out->color(210)->write('But then I');
launchThisMess();