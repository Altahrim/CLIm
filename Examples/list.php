#!/usr/bin/php
<?php
use \CLIm\Widgets\BulletList;

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

$list = new BulletList();
$list->draw($data);
