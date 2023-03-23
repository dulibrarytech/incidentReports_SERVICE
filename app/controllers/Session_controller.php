<?php

/*
 * Session controller functions
 */

class Session_controller
{
    public $session_model;
    public $app;

    function __construct($app) {

    	$this->session_model = new Session($app);
    	$this->app = $app;
        $this->logger = new SimpleLogger();
        $this->settings = $app->config('ir_settings');
    }

    public function validateSession() {

        $token = $this->session_model->validate_token();
        if($token !== false) {

            echo $token;
        }
        else {

            echo "invalid";
        }
        
    }

    public function validateToken() {
        $response = [];
        $token = trim($this->app->request->get('token'));
        if($token) {
            $response['token'] = $token;
            $data = $this->session_model->validate_token_string($token);

            if($data) {
                $response['profile'] = $data;
            }

            $response['status'] = "success";
        }
        else {
            $response['status'] = "error";
            $response['error'] = "Token not found";
        }

        echo json_encode($response);
    }

    public function authenticateLogin() {

        // Get creds from POST
        $username = trim($this->app->request->post('username'));
	    $password = trim($this->app->request->post('password'));
		
        // Sanitize data
        $username = htmlspecialchars($username, ENT_QUOTES);
        $password = htmlspecialchars($password, ENT_QUOTES);

        if($this->session_model->ldap_authenticate($username,$password)) {
            $id = $this->session_model->ir_authenticate($username);
            if($id !== false) {
                $profileArr = $this->session_model->get_user_profile($id);
                
                // Build return array
                $auth = array();
                $auth['token'] = $this->session_model->create_user_token($id, $profileArr);
                $auth['profile'] = $profileArr ;
                $auth['status'] = "success";
            }
            else {
                $auth['status'] = "error";
            }
        } 
        else {
            $auth['status'] = "error";
        }

        echo json_encode($auth);
    }

    public function authenticateSSO() {

        // Get data from SSO IDP request
        $username = $this->app->request->post('employeeID');
        $host = $this->app->request->post('HTTP_HOST');

        if($host == $this->settings['sso_host']) {

            $id = $this->session_model->ir_authenticate($username);
            if($id !== false) {
                $profileArr = $this->session_model->get_user_profile($id);
                
                $auth = array();
                $auth['token'] = $this->session_model->create_user_token($id, $profileArr);
                $auth['profile'] = $profileArr ;
                $auth['status'] = "success";

                $url = $this->settings['sso_client_login_url'] . "/" . $auth['token'];
                header("Location: " . $url);
                die();
            }
            else {
                $auth['status'] = "error";
                echo json_encode($auth);
            }
        }
        else {
            $auth['status'] = "error";
            echo json_encode($auth);
        }
    }
}
