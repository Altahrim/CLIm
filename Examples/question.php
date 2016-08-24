#!/usr/bin/php
<?php
use \CLIm\Widgets\Question;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Vendor' .  DIRECTORY_SEPARATOR . 'autoload.php';

$out = CLIm::getInstance();
$out->clear();

$prompt = (new Question\Select())
    ->setText('I let you choose the next prompt')
    ->setId('PromptStyle')
    ->addChoice('~>', '>>', '#', ':::')
    ->setDefault('1')
    ->getAnswer();

$out->writeLn('OK, you choose to use this one: %s', $prompt);

$mood = (new Question())
    ->setText('New, feel free to write something')
    ->setId('Mood')
    ->setDefault('Hmm, I am not in the mood to write something…')
    ->getAnswer();

$out->writeLn('“%s”, how cute ☺', $mood);

$mood = (new Question\Character())
    ->setText('Please press “q” to quit…')
    ->setValidChars('qQ')
    ->getAnswer();

$out
    ->write('Thanks!')
    ->lf();
