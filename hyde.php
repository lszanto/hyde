#!/usr/bin php
<?php

// composer
require __DIR__ . '/vendor/autoload.php';

// grab yaml component
use Symfony\Component\Yaml\Yaml;

// logger fns
function info($str) {
    echo "\033[0;32mHyde >> {$str}\n\033[0m";
}
function err($str) {
    echo "\033[0;31mHyde !! {$str}\n\033[0m";
}

info('Welcome to Hyde Static Site Generator!');
info('Begin by parsing parts');

// grab variables to send to build
$variable_helper = array();

// set site file(helper variables basically)
$site_file = __DIR__ . '/parts/site.yaml';

// check for site.yaml
if(is_file($site_file)) {
    info("Found site.yaml, parsing into variables");

    try {
        // parse into site variables
        $variable_helper['site'] = Yaml::parse(file_get_contents($site_file), true);
    } catch(Exception $e) {
            err("Error parsing file " . $site_file);
            err($e->getMessage());
            die("Exiting...\n");
    }
}

// grab anything from parts directory
$parts = glob(__DIR__ . '/parts/*', GLOB_ONLYDIR);

info('Loading parts directories from ' . __DIR__ . '/parts');

// loop through any parts
foreach($parts as $part) {
    // set folder name
    $folder = basename($part);

    info(" - Found folder '{$folder}', parsing now");

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

        // if we're in config ignore
        if(basename($part_file) == 'config.yaml') {
            $i++;
            continue;
        }

        info(" -- Parsing " . basename($part_file) . " into variables");

        // try to parse the file
        try {
            $variable_helper[$folder][basename($part_file, '.yaml')] = Yaml::parse(file_get_contents($part_file), true);
        } catch(Exception $e) {
            err("Error parsing file " . $part_file);
            err($e->getMessage());
            die("Exiting...\n");
        }

        // next file
        $i++;
    } while($i < $c);

    // config file
    $config_file = $part . '/config.yaml';

    // check for config
    if(is_file($config_file)) {
        info(" - Found config file {$folder}/config.yaml");

        // parse config
        $config = Yaml::parse(file_get_contents($config_file), true);

        // check if they want to sort
        if(array_key_exists('sort_by', $config) && array_key_exists('sort_order', $config)) {
            info(" --- Sorting {$folder} by {$config['sort_by']} field in {$config['sort_order']} order");

            // sort field
            usort($variable_helper[$folder], function($a, $b) use($config) {
                // set fields
                $field_a = $a[$config['sort_by']];
                $field_b = $b[$config['sort_by']];

                // check if we're comparing dates
                if(stripos($config['sort_by'], 'date') !== false) return ($config['sort_order'] == 'desc') ? (strtotime($field_b) - strtotime($field_a)) : (strtotime($field_a) - strtotime($field_b));
                else return ($config['sort_order'] == 'desc') ? ($field_b - $field_a) : ($field_a - $field_b);
            });
        }

        info(" -- Applying config to {$folder}");
        //print_r($variable_helper[$folder]);
    }
}

info("Finished parsing parts, moving onto site generation");

info("Setting up Twig to parse templates");

// create twig loader
$loader = new Twig_Loader_Filesystem(__DIR__ . '/site');

// create twig environment
$twig = new Twig_Environment($loader);

// grab site pages
$site_pages = glob(__DIR__ . '/site/*.html');

info("Found " . count($site_pages) . " page(s) in " . __DIR__ . "/site");

// first create links
foreach($site_pages as $site_page) {
    $variable_helper['links'][basename($site_page, '.html')]['url'] = basename($site_page);
    $variable_helper['links'][basename($site_page, '.html')]['title'] = basename($site_page, '.html');
    $variable_helper['link'][basename($site_page, '.html')] = basename($site_page);
}

// loop through pages
foreach($site_pages as $site_page) {
    // grab page
    $page = basename($site_page);

    info(" - Rendering {$page}");

    // write file
    $fh = fopen(__DIR__ . '/build/' . $page, 'w');
    fwrite($fh, $twig->render($page, $variable_helper));
    fclose($fh);
}
