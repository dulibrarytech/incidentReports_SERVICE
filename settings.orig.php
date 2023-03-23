<?php 

/*
* settings.php
*
* Environment-specific settings for the Incident Reports application
*/

require('./SimpleLogger.php');
$logger = new SimpleLogger();
$logger->log("TESTING LOG");

$settings = array();

$settings['base_url'] = "/var/www/html/lib.moon.du.edu/incidentReports_SERVICE/";

$settings['db_server'] = 'localhost';
$settings['db_user'] = 'root'; 
$settings['db_pass'] = 'l1nux4l1f3';
$settings['db_name'] = 'incidentreportsdb';

$settings['enable_test_route'] = false;
