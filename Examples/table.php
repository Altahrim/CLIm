#!/usr/bin/php
<?php
use \Clim\Widget\Table;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Vendor' .  DIRECTORY_SEPARATOR . 'autoload.php';

$out = Clim::getInstance();
$out->clear();

$data = [
    ['You', 'can', 'use', 'some tables'],
    ['Tables', 'are', 'very convenient']
];

$t = new Table();
$t->addData($data);

$out->writeLn('Table with borders:');
$t->draw();
$out->lf();

$out->writeLn('Same table without borders:');
$t->draw(Table::DISP_ROWS|Table::DISP_COLS);
$out->lf();

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
$out->lf();

$out->writeLn('Multilines and array display:');
$t = new Table();
$t->addData([
    ["If\nyou want\nto use\nit", 'with', "multiple\nline"],
    ['in', 'multiple', 'rows'],
    ['new lines', 'are', "also\ndetected"],
    ['', 'and', ['arrays', 'arrays', 'arrays']]
]);
$t->draw();

$out->lf();
