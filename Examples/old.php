#!/usr/bin/php
<?php
use CLIm\Helpers\Colors;
use CLIm\Helpers\Style;
use CLIm\Widgets\ProgressBar;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Vendor' .  DIRECTORY_SEPARATOR . 'autoload.php';

error_reporting(-1);
ini_set('display_errors', false);

$out = CLIm::getInstance();

$out->setPrompt('~>', 214, null, Style::BOLD);

$color = isset($argv[1]) ? $argv[1] : 0;

$out->clear();

$out->info();
$out->skipLn(2);

$out
    ->write('Hello ')
    ->style(Style::BOLD)
    ->color(Colors::CYAN)
    ->writeLn('World')
    ->reset()
    ->skipLn(2);

$out->testColors();

$out
    ->color(148)
    ->write('Yellow ')
    ->color('#999999')
    ->write('Gray ')
    ->color(99)
    ->write('Magenta ')
    ->color(122)
    ->write('Cyan ')
    ->color(42)
    ->write('Green ')
    ->color(9)
    ->write('Red ')
    ->color(111)
    ->write('Blue ')
    ->color(255)
    ->writeLn('White')
    ->reset();

$out->registerErrorHandlers();
class machin {
    private static function hidden() {
        throw new \Exception('Kikooooo ! Je suis une exception exceptionnante', 42);
    }
    public static function bla() {
        self::hidden();
    }
}

function except() {
    machin::bla(1, 2, 'abc');
}
//except();

$out
    ->write('You choose ')
    ->color($color)
    ->write('color ' . $color)
    ->bgColor($color)
    ->write('      ')
    ->reset()
    ->skipLn(2);

$out->table([['Toumtoumtoum', 'Toummmm', 'Toum'], ['Touuuuum', 'b' => 'Boum', 'Toooooooooooum', 'Truc bidule chouette'], ['Toum', 'Tium', 'b' => 'Tum']]);

$out->skipLn(2);
$out->writeLn('Avancement :');
$progress = new ProgressBar($out);
$progress->init(1400);
$progress->setStep(35);
for ($i = 0; $i < 40; ++$i) {
    usleep(20000);
    $progress->nextStep();
}

$out->select('Alors ?', ['Oui', 'Non', 'Peut-être', 'Ce n\'est pas exactement cela']);
$out->ask('Guess what?');
