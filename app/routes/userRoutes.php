<?php

$user_controller = new User_controller($app);

$app->post('/reports/add', function() use ($app, $user_controller){	

	$user_controller->submitIncidentReport();
});

$app->post('/reports/edit', function() use ($app, $user_controller){	

	$user_controller->editIncidentReport();
});

$app->get('/reports/all', function() use ($app, $user_controller){	

	$user_controller->getReportData();
});

// Users
$app->get('/users/all', function() use ($app, $user_controller){	

	$user_controller->getUserData(); //
});

$app->post('/users/add', function() use ($app, $user_controller){	

	$user_controller->addUserData();
});

$app->put('/users/edit', function() use ($app, $user_controller){	

	$user_controller->editUserData(); //
});

$app->post('/users/remove', function() use ($app, $user_controller){	

	$user_controller->removeUserData();
});

// Search
$app->get('/search/id', function() use ($app, $user_controller){	

	$user_controller->getReportByID(); //
});

$app->get('/search/reports', function() use ($app, $user_controller){	

	$user_controller->searchReports(); //
});

$app->get('/search/complete', function() use ($app, $user_controller){	

	$user_controller->getAutoSuggest(); //
});


