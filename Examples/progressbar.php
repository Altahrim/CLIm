#!/usr/bin/php
<?php
use \Clim\Widget\ProgressBar;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Vendor' .  DIRECTORY_SEPARATOR . 'autoload.php';

$out = Clim::getInstance();
$p   = new ProgressBar();

// Gives best results
\Clim\Helper\Cursor::hide();

$out->writeLn('Progressbar:');

// Target
$p->init(666);

// Step size
$p->setStep(5);

// Do some stuff
while(!$p->nextStep()) {
    usleep(20000);
}

$out->line();

// With current action
$todo = [
    'Initialization…',
    'Inventing new steps',
    'Trying to look busy',
    'Launching `rm -rf /` on production servers',
    'Now searching a new job for you!',
    '' // An empty element remove the info
];

$p = new ProgressBar();
$p->init(count($todo) - 1);
foreach ($todo as $k => $action) {
    if ($k > 1) {
        usleep(rand(200000, 2000000));
    }
    $p->setCurrent($k, $action);
}

$out->line();

// With error
$p = new ProgressBar();
$p->init(count($todo));
foreach ($todo as $k => $action) {
    usleep(200000);
    if (4 === $k) {
        $p->setError();
        break;
    }
    $p->setCurrent($k, $action);
}