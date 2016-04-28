#!/usr/bin/php
<?php
use \CLIm\Helpers\Colors;
use \CLIm\Helpers\Prompt;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Vendor' .  DIRECTORY_SEPARATOR . 'autoload.php';

$out = CLIm::getInstance();
$out->clear();

$prompt = Prompt::select('I let you choose the next prompt', ['~>', '>>', '#', ':::']);
Prompt::setPrompt($prompt[1], Colors::CYAN);
$out->writeLn('Hmm, good choice, thanks!');
$out->line();

Prompt::ask('Now, feel free to write something');

$out->line();
$out->writeLn('Please press « q » to quit…');
Prompt::readChar(function ($c) {
    return 0 === strcasecmp($c, 'q');
});
$out->lf();
