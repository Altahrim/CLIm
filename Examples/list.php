#!/usr/bin/php
<?php
use \CLIm\Widgets\ItemList;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$out = CLIm::getInstance();
$out->clear();

$data = [
    'Today, I would like to introduce lists',
    'Lists can be useful' => [
        'to lists things',
        'for example' => [
            'this', 'is', 'another', 'list'
        ]
    ],
    'I could continue like this for a while'
];

$list = new ItemList();
$list->draw($data);
