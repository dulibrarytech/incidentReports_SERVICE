<?php

require "settings.php"; // Get the settings array 
$projectDir = $settings['base_url'];
require $projectDir . 'Slim/Slim.php';     //include the framework in the project

\Slim\Slim::registerAutoloader();       //register the autoloader

//$projectDir = '/Users/jeff.rynhart/Dev/Projects/incidentReports_SERVICE/';   //define the directory containing the project files
//$projectDir = getcwd();

require $projectDir . "includes.php";     //include the file which contains all the project related includes

$app = new \Slim\Slim(array(
    'debug' => true
));     //instantiate a new Framework Object 

$app->config('ir_settings', $settings); // Store the settings array

require $projectDir . "routes.php";       //include the file which contains all the routes/route inclusions

$app->run();                            //load the application
