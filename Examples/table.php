#!/usr/bin/php
<?php
use \CLIm\Widgets\Table;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Vendor' .  DIRECTORY_SEPARATOR . 'autoload.php';

$out = CLIm::getInstance();
$out->clear();

$data = [
    ['You', 'can', 'use', 'some tables'],
    ['Tables', 'are', 'very convenient']
];

$t = new Table();
$t->addData($data);

$out->writeLn('Table with borders:');
$t->draw();
$out->skipLn();

$out->writeLn('Same table without borders:');
$t->draw(Table::DISP_ROWS|Table::DISP_COLS);
$out->skipLn();

$out->writeLn('Named columns can be very helpful:');
$data = [
    ['col1' => 'Column order', 'col2' => 'is driven', 'col3' => 'by the appearance'],
    ['col1' => 'of columns in data'],
    ['col2' => 'can be reassembled', 'col1' => 'Content'],
    ['col3' => 'by this widget']
];
$t = new Table();
$t->addData($data);
$t->draw();
$out->skipLn();
