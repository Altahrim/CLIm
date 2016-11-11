#!/usr/bin/php
<?php
use Clim\Helper\Answer;
use Clim\Widget\Question;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Vendor' .  DIRECTORY_SEPARATOR . 'autoload.php';

$out = Clim::getInstance();
$out->verbosity(\Clim::VERB_QUIET);
$out->writeLn('It\'s possible to preload some answers so you can automate scripts');
$out->writeLn('For example, try to add some arguments to this script. Each argument will to answer to the next questions');
$out->line();

$answers = [];
array_shift($argv);
foreach ($argv as $k => $arg) {
    $answers['question_' . ($k + 1)] = $arg;
}
Answer::loadAnswsers($answers);

$out->writeLn('Protip: in quiet mode, already answered questions are not displayed');
$out->setScriptVerbosity(\Clim::VERB_QUIET);
$choices = ['Yes', 'No'];

$res = (new Question\Select())
    ->setText('Do you have a favorite color ?')
    ->setId('question_1')
    ->addChoice(... $choices)
    ->getAnswer();
$out->line();

$out->setScriptVerbosity(\Clim::VERB_NORMAL);
if ('Yes' === $res) {
    (new Question())
        ->setText('So, what\'s your favorite color ?')
        ->setId('question_2')
        ->getAnswer();
    $out->line();
}

$out->writeLn('Protip: in debug mode, you can see question IDs');
$out->setScriptVerbosity(\Clim::VERB_DEBUG);
(new Question())
    ->setText('What\'s your credit card number?')
    ->setId('question_3')
    ->getAnswer();
$out->line();


$out->setScriptVerbosity(\Clim::VERB_NORMAL);
(new Question\Hidden())
    ->setText('And what is its cryptogram ?')
    ->setId('question_4')
    ->getAnswer();
$out->writeLn("That's all");
