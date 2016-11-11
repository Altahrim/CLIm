#!/usr/bin/php
<?php
use \Clim\Helper\Color;
use \Clim\Helper\Style;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Vendor' .  DIRECTORY_SEPARATOR . 'autoload.php';

$out = Clim::getInstance();
$out->clear();

$out
    ->write('Your text can be ')
    ->style(Style::NEGATIVE)
    ->writeLn('styled')
    ->reset()
    ->write('A text can be ')
    ->style(Style::UNDERLINE)
    ->write('activated')
    ->style(Style::UNDERLINE, true)
    ->write(' and deactivated')
    ->lf(2);

$out
    ->style(Style::UNDERLINE)
    ->write('Several ')
    ->style(Style::BOLD)
    ->write('styles can be mixed')
    ->style(Style::BOLD, true)
    ->write(' and removed')
    ->reset()
    ->lf(2);

// Color can be:
// - a constant from Colors
// - a number between 0 and 255
// - an hexadecimal RGB

$out
    ->color(Color::BLUE)
    ->write('Colors ')
    ->color(Color::MAGENTA)
    ->write('can ')
    ->style(Style::BOLD)
    ->write('also ')
    ->color(214)
    ->write('be ')
    ->color('#AD54C2')
    ->writeLn('used.');
