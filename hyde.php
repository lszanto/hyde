<?php

// composer
require __DIR__ . '/vendor/autoload.php';

// grab site pages
$site_pages = glob(__DIR__ . '/site/*.html');

// create twig loader
$loader = new Twig_Loader_Filesystem(__DIR__ . '/site');

// create twig environment
$twig = new Twig_Environment($loader);

// grab variables to send to build
$variable_helper = array();

// grab anything from parts directory
$parts = glob(__DIR__ . '/parts/*', GLOB_ONLYDIR);

// loop through any parts
foreach($parts as $part) {
    // get varible to assign as
    $assign_as = basename($part);

    // create empty array
    $variable_helper[$assign_as] = array();

    // grab the files
    $part_files = glob($part . '/*.ini');

    // process each file
    foreach($part_files as $part_file) {
        // parse into existence
        $variable_helper[$assign_as][basename($part_file, '.ini')] = parse_ini_file($part_file, false, INI_SCANNER_TYPED);
    }
}

// write file
$fh = fopen(__DIR__ . '/build/' . 'index.html', 'w');
fwrite($fh, $twig->render('index.html', $variable_helper));

// close file
fclose($fh);