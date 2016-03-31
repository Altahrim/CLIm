#!/usr/bin/php
<?php
use \CLIm\Helpers\Colors;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Vendor' .  DIRECTORY_SEPARATOR . 'autoload.php';

$out = CLIm::getInstance();
$out->clear();

$prompt = $out->select('I let you choose the next prompt', ['~>', '>>', '#', ':::']);
$out->setPrompt($prompt[1], Colors::CYAN);

$out->writeLn('Thanks!');
$out->skipLn();
$out->ask('Now, feel free to write something');
$out->skipLn();
