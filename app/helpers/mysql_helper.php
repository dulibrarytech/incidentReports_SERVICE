<?php 

function connectToDB() {

	$app = \Slim\Slim::getInstance();

	$settings = $app->config('ir_settings');

	$server = $settings['db_server']; 
	$user = $settings['db_user'];
	$pass = $settings['db_pass'];
	$database = $settings['db_name'];
	$connection = new PDO('mysql:host=' . $server . ';dbname=' . $database . ';charset=utf8', $user, $pass);
	$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	return $connection;
}

