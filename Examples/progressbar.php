#!/usr/bin/php
<?php
use \CLIm\Widgets\ProgressBar;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Vendor' .  DIRECTORY_SEPARATOR . 'autoload.php';

$out = CLIm::getInstance();
$p   = new ProgressBar();

$out->writeLn('Progressbar:');

// Target
$p->init(666);

// Step
$p->setStep(5);

// Do some stuff
while(!$p->nextStep()) {
    usleep(40000);
}
