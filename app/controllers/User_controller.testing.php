<?php

/*
 * IR controller functions
 */

require_once('SimpleLogger.php');

class User_controller
{
    public $user_model;
    public $session_model;
    public $mysql_model;
    public $as_model;
    public $logger;

    function __construct($app) {

    	$this->user_model = new User($app);
        $this->session_model = new Session($app);
    	$this->app = $app;
	$this->logger = new SimpleLogger();
	$this->logger->log("UC Constructor1");
    }

    public function submitIncidentReport() {

    	$filteredData = array();
        $response = array();
        $formData = $this->app->request->post();
        $sendEmail = false;

        if(isset($formData['sendNotifications'])) {

            $sendEmail = $formData['sendNotifications'] == "true" ? true : false;
            unset($formData['sendNotifications']);
        }

        // Sanitize
        foreach($formData as $key => $value) {

        	$trimmed = trim($value);
            $filteredData[$key] = htmlspecialchars($trimmed, ENT_QUOTES);
        }

        // Save the report
        $trackingNumber = $this->user_model->save_incident_report($filteredData);
        if($trackingNumber === false) {

        	$response['status'] = "error";
            $response['emailStatus'] = "error";
        }
        else {

        	$response['status'] = "success";
            if($sendEmail === true) {

                // Send email notifications
                if($this->user_model->send_email_notifications($filteredData, $trackingNumber)) {
                    
                    $response['emailStatus'] = "success";
                }
                else {
                    $response['emailStatus'] = "error";
                }
            }
            else {
                $response['emailStatus'] = "disabled";
            }
        }

        echo json_encode($response);
    }

    public function getReportData() {

        $response = array(); // set response to error here
        $token = $this->session_model->validate_token();

        if($token !== false) {

            $data = $this->user_model->get_report_data_array();
            if($data !== false) {

                $response['status'] = "success";
                $response['data'] = $data;
                $response['token'] = $token;
            }
            else {

                $response['status'] = "error";
            }  
            echo json_encode($response);          
        }
        else {
            $this->app->halt(401, '<h3>401 Unauthorized</h3>');
        }
    }

    public function getUserData() {

        $response = array();

        $token = $this->session_model->validate_token();
        if($token !== false) {

            $data = $this->user_model->get_user_data_array();
            if($data !== false) {

                $response['status'] = "success";
                $response['data'] = $data;  // PP ?
                $response['token'] = $token;
            }
            else {

                $response['status'] = "error";
            }  
            echo json_encode($response);          
        }
        else {

            $this->app->halt(401, '<h3>401 Unauthorized</h3>');
        }
    }

    public function editUserData() {

        $response = array();
        $sanitized = array();
        $formData = $this->app->request->put();

        // Sanitize
        foreach($formData as $key => $value) {

            $trimmed = trim($value);
            $sanitized[$key] = htmlspecialchars($trimmed, ENT_QUOTES);
        }

        $token = $this->session_model->validate_token();
        if($token !== false) {

            $success = $this->user_model->edit_user_data($sanitized); // TODO remove this variable
            if($success === true) {

                $response['status'] = "success";
                $response['token'] = $token;
            }
            else {

                $response['status'] = "error";
            }  
            echo json_encode($response);          
        }
        else {

            $this->app->halt(401, '<h3>401 Unauthorized</h3>');
        }
    }

    public function removeUserData() {

        $response = array();

        $post = $this->app->request->post();
        $token = $this->session_model->validate_token();
        if($token !== false) {

            //$success = $this->user_model->delete_data($post['ID']); // TODO remove this variable
            //$success = true;
            //$response = array("rmd test SUCCESS");
            $response['status'] = $this->user_model->delete_data($post['ID']) === true ? "success" : "error";
            $response['token'] = $token; 
            //echo json_encode($response);          
        }
        else {

            $this->app->halt(401, '<h3>401 Unauthorized</h3>');
        }

        //$response['status'] = $this->user_model->delete_data($post['ID']) === true ? "success" : "error"; //DEBUG

        echo json_encode($response);  
    }

    public function addUserData() {

        $response = array();
        $sanitized = array();
        $newId = 0;
        $formData = $this->app->request->post();

        $token = $this->session_model->validate_token();
        if($token !== false) {

            $email = trim($formData['userEmail']);
            $type = trim($formData['sendType']);
            $admin = trim($formData['admin']);
            $uid = trim($formData['userName']);

            $sanitized['Email'] = htmlspecialchars($email, ENT_QUOTES);
            $sanitized['SendType'] = htmlspecialchars($type, ENT_QUOTES);
            $sanitized['isAdmin'] = htmlspecialchars($admin, ENT_QUOTES);
            $sanitized['LoginID'] = htmlspecialchars($uid, ENT_QUOTES);

            $response['debug'] = print_r($sanitized,1);

            $newId = $this->user_model->add_user_data($sanitized); // TODO remove this variable
            if($newId > 0) {

                $response['status'] = "success";
                $response['token'] = $token;
            }
            else {

                $response['status'] = "error";
            }  
            echo json_encode($response);          
        }
        else {

            $this->app->halt(401, '<h3>401 Unauthorized</h3>');
        }
    }

    public function getReportByID() {

        $token = $this->session_model->validate_token();
        if($token !== false) {

            $request = $this->app->request->get();
            $id = trim($request['id']);
            if(is_numeric($id) === true) {

                $record = $this->user_model->get_report_by_id($id);
                if($record !== false) {

                    $response['status'] = "success";
                    $response['token'] = $token;
                    $response['data'] = $record;
                }
                else {

                    $response['status'] = "error";
                } 
                echo json_encode($response);
            }
            else {

                $this->app->halt(400, '<h3>400 Bad Request</h3>');
            }
        }
        else {

            $this->app->halt(401, '<h3>401 Unauthorized</h3>');
        }
    }

    public function searchReports() {

        $token = $this->session_model->validate_token();
        if($token !== false) {

            $response = array();
            $sanitized = array();
            $request = $this->app->request->get();

            // Sanitize
            foreach($request as $key => $value) {

                $trimmed = trim($value);
                $sanitized[$key] = htmlspecialchars($trimmed, ENT_QUOTES);
            }

            if(isset($sanitized['fromDate']) === false) {
                $sanitized['fromDate'] = null;
            }
            if(isset($sanitized['toDate']) === false) {
                $sanitized['toDate'] = null;
            }

            $reports = $this->user_model->search_reports($sanitized['offenseType'],$sanitized['fromDate'],$sanitized['toDate']);
            if($reports !== false) {

                $response['status'] = "success";
                $response['token'] = $token;
                $response['data'] = $reports;
            }
            else {

                $response['status'] = "error";
            } 
            echo json_encode($response);
        }
        else {

            $this->app->halt(401, '<h3>401 Unauthorized</h3>');
        }
    }

    public function editIncidentReport() {

        $response = array();

        $token = $this->session_model->validate_token();
        if($token !== false) {

            $formData = $this->app->request->post();

            // Sanitize
            foreach($formData as $key => $value) {

                $trimmed = trim($value);
                $filteredData[$key] = htmlspecialchars($trimmed, ENT_QUOTES);
            }
            
            $id = $filteredData['reportID'];
            unset($filteredData['reportID']);  // Remove this from the form data, so the form fields match the database fields
            $success = $this->user_model->update_report($id,$filteredData);
            //$response['dev'] = print_r($formData,1);
            if($success === true) {

                $response['status'] = "success";
                $response['token'] = $token;
            }
            else {

                $response['status'] = "error";
            }  
            echo json_encode($response);          
        }
        else {

            $this->app->halt(401, '<h3>401 Unauthorized</h3>');
        }
    }

    public function getAutoSuggest() {   

        if(is_ajax()) {
            $response = array();
            $fieldList = htmlspecialchars($this->app->request->get('fields')); 
            $fieldArray = explode(',', $fieldList);

            $results = $this->user_model->get_field_data($fieldArray);
            if($results !== false) {
                $response['status'] = "success";
                $response['data'] = $results;
            }
            else {
                $response['status'] = "error";
            }
            echo json_encode($response);
        }
        else {
            $this->app->halt(403, '<h3>403 Forbidden</h3>');
        }
    }
}
