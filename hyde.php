<?php

// composer
require __DIR__ . '/vendor/autoload.php';

// grab site pages
$site_pages = glob(__DIR__ . '/site/*.html');

// create twig loader
$loader = new Twig_Loader_FileSystem(__DIR__ . '/site');

// create twig environment
$twig = new Twig_Environment($loader);

// write file
$fh = fopen(__DIR__ . '/build/' . 'index.html', 'w');
fwrite($fh, $twig->render('index.html', array('party' => 'I like to move it move itz')));

// close file
fclose($fh);