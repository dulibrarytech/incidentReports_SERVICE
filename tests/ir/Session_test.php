<?php

class Session_test
{
    public $session_model;

    function __construct($app) {

    	$this->session_model = new Session($app);
    	$this->app = $app;
    }

    public function test_url() {

        echo getcwd();
    }

    public function test_validate_token() {

    	$token = $this->session_model->create_user_token(214);
    	$validated = $this->session_model->validate_token($token);
    	if($validated !== false) {

    		echo $validated;
    		//echo $this->session_model->get_token_timestamp($validated);
    	}
    	else {

    		echo "FALSE";
    	}
    }

    public function test_get_user_profile() {

        echo print_r($this->session_model->get_user_profile("15"),1);
    }

    public function test_ir_authenticate() {

        echo $this->session_model->ir_authenticate("jeff.rynhart") === false ? "FALSE" : "VALID";
    }

    public function test_ldap_authenticate() {

        //echo $this->session_model->ldap_authenticate("jeff.rynhart","password") === false ? "FALSE" : "VALID";
       // echo $this->session_model->ldap_authenticate("jeff.rynhart","elementCLL003@") === false ? "FALSE" : "VALID";
        if($this->session_model->ldap_authenticate("DUID","PWD") == true) {
            echo "VALID";
        }
        else {
            echo "INVALID...";
        }
    }
}