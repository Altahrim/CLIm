#!/usr/bin/env php
<?php
use \Clim\Helper\Style;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Vendor' .  DIRECTORY_SEPARATOR . 'autoload.php';

$out = Clim::getInstance();

// Display all 256 colors
// Foreground first
$out
    ->style(Style::UNDERLINE)
    ->writeLn('## Foreground colors:')
    ->style(Style::UNDERLINE, true)
    ->lf();
for ($i = 0; $i < 256;) {
    $out
        ->color($i)
        ->write(sprintf('%4s ', $i));
    if (0 === (++$i % 8)) {
        $out->reset()->lf();
    }
}
$out->lf()->line()->lf();

// Then background
$out
    ->style(Style::UNDERLINE)
    ->writeLn('## Background colors:')
    ->style(Style::UNDERLINE, true)
    ->lf();
for ($i = 0; $i < 256;) {
    $out
        ->bgColor($i)
        ->write(sprintf('%4s ', $i));
    if (0 === (++$i % 8)) {
        $out->reset()->lf();
    }
}
$out->lf();
