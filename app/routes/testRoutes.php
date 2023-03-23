<?php

/* 
 * Incident Reports App
 *
 * Test API
 */

/*
 * Output a view with test name and test result data
 *
 */
$app->get('/test/all', function() use ($app){	

    $settings = $app->config('ir_settings');
    if($settings['enable_test_route'] === true) {
        
        // user
        $run_save_incident_report 	= false;
        $run_get_report_data_array	= true;
        $run_send_email_notifications  = false; 
        $run_add_user_data          = false;
        $run_edit_user_data     = false;

        $run_get_report_by_id       = false; 
        $run_search_reports    = false;
        $run_update_report      =   false;

        // session
        $run_validate_token         = false;
        $run_test_url               = false;
        $run_ldap_authenticate      = false;

        // email
        $run_get_email_list         = false;
        $run_send_email             = false;

        // autocomplete
        $run_get_field_data    =  false;

        $user_test = new User_test($app);
        $session_test = new Session_test($app);
        $email_test = new Email_test($app);

        echo "Running IncidentReports tests...<br /><br />";

        if($run_test_url) {

            echo "Test URL: ";
            echo $session_test->test_url();
            echo "<br />";
        }

        if($run_validate_token) {

            echo "Session::validate_token(): ";
            echo $session_test->test_validate_token();
            echo "<br />";
        }

        if($run_ldap_authenticate) {

            echo "User::ldap_authenticate(): ";
            echo $session_test->test_ldap_authenticate();
            echo "<br />";
        }

        if($run_save_incident_report) {

        	echo "User::save_incident_report(): ";
        	echo $user_test->test_save_incident_report();
        	echo "<br />";
        }

        if($run_get_report_data_array) {

        	echo "User::get_report_data_array(): ";
        	echo $user_test->test_get_report_data_array();
        	echo "<br />";
        }

        if($run_send_email_notifications) {

            echo "User::send_email_notifications(): ";
            echo $user_test->test_send_email_notifications();
            echo "<br />";
        }

        if($run_add_user_data) {

            echo "User::add_user_data(): ";
            echo $user_test->test_add_user_data();
            echo "<br />";
        }

        if($run_edit_user_data) {

            echo "User::edit_user_data(): ";
            echo $user_test->test_edit_user_data();
            echo "<br />";
        }

        if($run_get_email_list) {

            echo "Email::get_email_list(): ";
            echo $email_test->test_get_email_list();
            echo "<br />";
        }

        if($run_send_email) {

            echo "Email::send_email_notifications(): ";
            echo $email_test->test_send_email();
            echo "<br />";
        }

        if($run_get_report_by_id) {

            echo "User::get_report_by_id(): ";
            echo $user_test->test_get_report_by_id();
            echo "<br />";
        }

        if($run_search_reports) {

            echo "User::search_reports(): ";
            echo $user_test->test_search_reports();
            echo "<br />";
        }

        if($run_update_report) {

            echo "User::update_report(): ";
            echo $user_test->test_update_report();
            echo "<br />";
        }

        if($run_get_field_data) {

            echo "User::get_field_data(): ";
            echo $user_test->test_get_field_data();
            echo "<br />";
        }
    }
    else {
        $app->halt(403, '<h3>403 Forbidden</h3>');
    }
});
