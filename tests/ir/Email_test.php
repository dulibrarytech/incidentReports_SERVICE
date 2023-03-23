<?php

/*
 * Tests Email.php functions
 *
 */

class Email_test
{
    public $email_model;

    function __construct($app) {

    	$this->email_model = new Email($app);
    	$this->app = $app;
    }

    public function test_get_email_list() {

        $retArray = $this->email_model->get_email_list();
        echo print_r($retArray,true);
    }

    public function test_send_email() {

        $list = array();
        $list['jeff.rynhart@du.edu'] = 'to';
        echo $this->email_model->send_email($list, "TEST IR EMAIL") === true ? "TRUE" : "FALSE";
    }
}