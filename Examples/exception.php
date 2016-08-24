#!/usr/bin/php
<?php
use \CLIm\Helpers\Colors;
use \CLIm\Helpers\Style;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Vendor' .  DIRECTORY_SEPARATOR . 'autoload.php';

$out = CLIm::getInstance();
$out->clear();

// You have to register handlers
$out->registerErrorHandlers();

// Then, build a nice application...
class Test
{
    public function doSomething()
    {
        self::doSomethingNasty(':)', ['This', 'is', 'the', 'end'], 13.37);
    }

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