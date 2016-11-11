#!/usr/bin/php
<?php
use Clim\Widget\Usage;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Vendor' .  DIRECTORY_SEPARATOR . 'autoload.php';

$out = Clim::getInstance();

$u = new Usage();
$u->setName(basename(__FILE__, '.php'));
$u->setShortDesc('Useful command to do nothing.');
$u->setLongDesc('As I previously stated, this command is really useless. Some tried to use it but each time, it does absolutely nothing.');
$u->addSection('Remarks:', 'I used this script… once…');
$u->setVersion('5.4.322 Early-access tech-preview');
$u->addUsage('');
$u->addUsage('[OPTIONS] [useless argument]');
$u->addOption([
    'short' => '-v',
    'desc' => 'Increase script verbosity',
    'long' => '--verbose'
]);
$u->addOption([
    'short' => '-r',
    'desc' => 'Still does nothing, but recursively',
]);
$u->addOption([
    'desc' => 'This is not even an option but it helps me to test',
]);
$u->addOption([
    'short' => '-q',
    'desc' => 'Reduce script verbosity',
    'long' => '--quiet'
]);
$u->addOption([
    'desc' => 'Explicitly does nothing',
    'long' => '--dry-run'
]);
$u->draw();