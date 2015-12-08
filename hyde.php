<?php

// composer
require __DIR__ . '/vendor/autoload.php';

// grab yaml component
use Symfony\Component\Yaml\Yaml;

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
    // set folder name
    $folder = basename($part);

    // create empty array with name of directory
    $variable_helper[$folder] = array();

    // grab the files
    $part_files = glob($part . '/*.yaml');

    // set loop start and count
    $i = 0;
    $c = count($part_files);

    // process each file
    do {
        // get the file
        $part_file = $part_files[$i];

        // try to parse the file
        try {
            $variable_helper[$folder][basename($part_file, '.yaml')] = Yaml::parse(file_get_contents($part_file), true);
        } catch(Exception $e) {
            die('Uh oh there has been an error trying to parse some of your parts, message: ' . $e->getMessage());
        }

        // next file
        $i++;
    } while($i < $c);
}

// write file
$fh = fopen(__DIR__ . '/build/' . 'index.html', 'w');
fwrite($fh, $twig->render('index.html', $variable_helper));

// close file
fclose($fh);