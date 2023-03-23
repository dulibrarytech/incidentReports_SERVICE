<?php

/*
 * Tests User.php functions
 *
 */

class User_test
{
    private $user_model;
    private $email_model;
    private $sql_model;

    function __construct($app) {

    	$this->user_model = new User($app);
        $this->email_model = new Email($app);
    	$this->app = $app;
    }

    public function test_save_incident_report() {

		$testReport = array_fill_keys(
            array('dateOfReport', 'reportCompletedBy', 'title', 'department', 'extension', 'dateOfOffense', 'timeOfOffense', 'wasCampusSafetyNotified', 'natureOfOffense', 'locationOfOffense', 'offenseNarrative', 'victimName', 'victimGender', 'victimApproximateAge', 'victimUniversityAffiliation', 'victimRace', 'suspectName', 'suspectGender', 'suspectApproximateAge', 'suspectRace', 'suspectUniversityAffiliation', 'suspectClothing', 'suspectHair', 'suspectApproximateHeight', 'suspectApproximateWeight',  'suspectGlasses', 'suspectFacialHair', 'otherPhysicalCharacteristics'), 'test data');
		$testReport['dateOfReport'] = "3/21/2015";
        $testReport['dateOfOffense'] = "3/25/2015";
        $testReport['title'] = "TEST";
        $testReport['department'] = "TEST";
        $testReport['extension'] = "TEST";
        $testReport['dateOfOffense'] = "12/12/2015";
        $testReport['timeOfOffense'] = time();
        $testReport['wasCampusSafetyNotified'] = "TEST";
        $testReport['natureOfOffense'] = "TEST";
        $testReport['locationOfOffense'] = "TEST";
        $testReport['offenseNarrative'] = "TEST";
        $testReport['victimName'] = "TEST";
        $testReport['victimGender'] = "TEST";
        $testReport['victimApproximateAge'] = "TEST";
        $testReport['victimUniversityAffiliation'] = "TEST";
        $testReport['victimRace'] = "TEST";
        $testReport['suspectName'] = "TEST";
        $testReport['suspectGender'] = "TEST";
        $testReport['suspectApproximateAge'] = "TEST";
        $testReport['suspectRace'] = "TEST";
        $testReport['suspectUniversityAffiliation'] = "TEST";
        $testReport['suspectClothing'] = "TEST";
        $testReport['suspectHair'] = "TEST";
        $testReport['suspectApproximateHeight'] = "TEST";
        $testReport['suspectApproximateWeight'] = "TEST";
        $testReport['suspectGlasses'] = "TEST";
        $testReport['suspectFacialHair'] = "TEST";
        $testReport['otherPhysicalCharacteristics'] = "TEST";

    	$result = $this->user_model->save_incident_report($testReport);
    	
        if($result === false || ctype_digit($result) === false) {

    		echo "Fail";
    	}
    	else {

    		echo "Pass: New ID: " . $result;
    	}
    }

    public function test_get_report_data_array() {

        $dataArray = $this->user_model->get_report_data_array();
        echo $dataArray === false ? "FALSE" : print_r($dataArray[0],true);
    }

    public function test_send_email_notifications() {

        // *Fill all array values with "test-data", then add dates where required
        $testReport = array_fill_keys(
            array('dateOfReport', 'reportCompletedBy', 'title', 'department', 'extension', 'dateOfOffense', 'timeOfOffense', 'wasCampusSafetyNotified', 'natureOfOffense', 'locationOfOffense', 'offenseNarrative', 'victimName', 'victimGender', 'victimApproximateAge', 'victimUniversityAffiliation', 'victimRace', 'suspectName', 'suspectGender', 'suspectApproximateAge', 'suspectRace', 'suspectUniversityAffiliation', 'suspectClothing', 'suspectHair', 'suspectApproximateHeight', 'suspectApproximateWeight',  'suspectGlasses', 'suspectFacialHair', 'otherPhysicalCharacteristics'), 'test data');
        $testReport['dateOfReport'] = "3/21/2015";
        $testReport['dateOfOffense'] = "3/25/2015";

        echo $this->user_model->send_email_notifications($testReport, "123") === true ? "TRUE" : "FALSE";
    }

    public function test_add_user_data() {

        $testData['Email'] = "testUser@example.com";
        $testData['SendType'] = "cc";
        $testData['isAdmin'] = "Yes";
        $testData['LoginID'] = "testUser";

        echo "New emailID: " . $this->user_model->add_user_data($testData) . "\n";
    }

    public function test_edit_user_data() {

        $testData['Email'] = "testUser_edit_@example.com";
        $testData['SendType'] = "cc";
        $testData['isAdmin'] = "Yes";
        $testData['LoginID'] = "testuser";
        $testData['EmailID'] = "53";

        if($this->user_model->edit_user_data($testData)) {
            echo "SUCCESS";
        }
        else {

            echo "FAIL";
        }
    }

    public function test_get_report_by_id() {

        echo "Record: " . print_r($this->user_model->get_report_by_id("6"),1);
    }

    public function test_search_reports() {

        $result = $this->user_model->search_reports("", "2010/9/29", "2010/10/02");
        if($result !== false) {
            echo isset($result['MESSAGE']) ? $result['MESSAGE'] : print_r($result,1);
        }
        else {
            echo "FAIL";
        }
    }

    public function test_update_report() {

        $testReport = array_fill_keys(
            array('victimName', 'victimGender', 'victimApproximateAge', 'victimUniversityAffiliation', 'victimRace', 'suspectName', 'suspectGender', 'suspectApproximateAge', 'suspectRace', 'suspectUniversityAffiliation', 'suspectClothing', 'suspectHair', 'suspectApproximateHeight', 'suspectApproximateWeight',  'suspectGlasses', 'suspectFacialHair', 'otherPhysicalCharacteristics'), 'test data');

        $testReport['victimName'] = "TEST";
        $testReport['victimGender'] = "TEST";
        $testReport['victimApproximateAge'] = "TEST";
        $testReport['victimRace'] = "TEST";
        $testReport['victimUniversityAffiliation'] = "TEST";
        $testReport['suspectName'] = "TEST";
        $testReport['suspectGender'] = "TEST";
        $testReport['suspectApproximateAge'] = "TEST";
        $testReport['suspectRace'] = "TEST";
        $testReport['suspectUniversityAffiliation'] = "TEST";
        $testReport['suspectClothing'] = "TEST";
        $testReport['suspectHair'] = "TEST";
        $testReport['suspectApproximateHeight'] = "TEST";
        $testReport['suspectApproximateWeight'] = "TEST";
        $testReport['suspectFacialHair'] = "TEST";
        $testReport['suspectGlasses'] = "TEST";
        $testReport['otherPhysicalCharacteristics'] = "TEST";
        //$testReport['reportID'] = "143";

        $success = $this->user_model->update_report("143",$testReport);

        if($success === true) {
            echo "SUCCESS";
        }
        else {
            
            echo isset($success['MESSAGE']) ? $success['MESSAGE'] : "FAIL";
        }
    }

    public function test_get_field_data() {

        $testFields = array('ReportCompletedBy', 'Title');
        echo print_r($this->user_model->get_field_data($testFields),1);
    }
}