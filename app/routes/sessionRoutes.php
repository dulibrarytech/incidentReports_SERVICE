<?php

$session_controller = new Session_controller($app);

$app->get('/', function() use ($app) {
     
     $app->halt(403, '<h3>403 Forbidden</h3>');
});

$app->get('/debugtest', function() use ($app) {

     echo "Test	ok";
});


$app->post('/session/validate', function() use ($app, $session_controller) { 

    $session_controller->validateSession();
});

$app->post('/login/authenticate', function() use ($app, $session_controller) {

    $session_controller->authenticateLogin();
});

$app->post('/login/sso', function() use ($app, $session_controller) {

    $session_controller->authenticateSSO();
});

$app->get('/login/validateToken', function() use ($app, $session_controller) {

    $session_controller->validateToken();
});