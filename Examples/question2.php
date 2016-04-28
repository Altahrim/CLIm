#!/usr/bin/php
<?php
use \CLIm\Helpers\Prompt;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Vendor' .  DIRECTORY_SEPARATOR . 'autoload.php';

$out = CLIm::getInstance();
$out->verbosity(\CLIm::VERB_QUIET);
$out->writeLn('It\'s possible to preload some answers so you can automate scripts');
$out->writeLn('For example, try to add some arguments to this script. Each argument will to answer to the next questions');
$out->line();

$answers = [];
array_shift($argv);
foreach ($argv as $k => $arg) {
    $answers['question_' . ($k + 1)] = $arg;
}
Prompt::loadAnswsers($answers);


$out->writeLn('Protip: in quiet mode, already answered questions are not displayed');
$out->setScriptVerbosity(\CLIm::VERB_QUIET);
$choices = ['yes' => 'Yes', 'No'];
$res = Prompt::select('Do you have a favorite color ?', $choices, 'question_1');
$out->line();

$out->setScriptVerbosity(\CLIm::VERB_NORMAL);
if ('yes' === $res[0]) {
    Prompt::ask('So, what\'s your favorite color ?', 'question_2');
    $out->line();
}

$out->writeLn('Protip: in debug mode, you can see question IDs');
$out->setScriptVerbosity(\CLIm::VERB_DEBUG);
Prompt::ask('What\'s your credit card number?', 'question_3');
$out->line();


$out->setScriptVerbosity(\CLIm::VERB_NORMAL);
$res = Prompt::hidden('And what is its cryptogram ?', true, 'question_4');

