<?php

/**
 * Incident Reports App
 *
 * Service Model functions
 * PHP version 5
 *
 * LICENSE: 
 *
 * @category   IncidentReports User Service Management
 * @package    
 * @author     Jeff Rynhart <jeff.rynhart@du.edu>
 * @copyright  2015 University of Denver
 */

require_once 'app/libs/JWT/JWT.php';
//require_once 'app/libs/mySQL.php';
//require_once 'app/models/Email.php';

use Guzzle\Http\Client;

class User
{
    public $app;
    private $db;
    private $logger;
    private $email;

    /**
     * Constructor
     *
     * @param Slim instance $app 
     */
    function __construct($app) {

        $this->app = $app;
        $this->db = connectToDB();
        $this->email = new Email($app);
        //$this->logger = new SimpleLogger();
    }

    /**
     * Creates a new record in the database 
     * 
     * @param array $report  Data for the new DB record.  Incude all fields to be written to the DB.  Use DB field name (first letter lowercase) as array key 
     *                                                      *Omit ReportID (primary key) and Timestamp
     *                                                     
     * @return bool  FALSE if transaction is unsuccessful; integer ReportID of new database record if successful 
     */
    public function save_incident_report($report) {

        $returnValue = false;
        $success = false;

        // Bind report data to statement.  
        $qString = "INSERT INTO reports (DateOfReport, ReportCompletedBy, Title, Department, Extension, DateOfOffense, TimeOfOffense, WasCampusSafetyNotified, NatureOfOffense, 
                    LocationOfOffense, OffenseNarrative, VictimName, VictimGender, VictimApproximateAge, VictimUniversityAffiliation, VictimRace, SuspectName, 
                    SuspectGender, SuspectApproximateAge, SuspectRace, SuspectUniversityAffiliation, SuspectClothing, SuspectHair, SuspectApproximateHeight, 
                    SuspectApproximateWeight, SuspectGlasses, SuspectFacialHair, OtherPhysicalCharacteristics) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmt = $this->db->prepare($qString);
        $count = 1;
        foreach($report as $key => $value) {

            if($count == 1 || $count == 6) {   // First database field to write, 

                // convert date strings to date
                $time = strtotime($value);
                $value = date("Y-m-d", $time);
            }
            $stmt->bindParam($count, trim($value));
            $count++;
        }
        try {

            $success = $stmt->execute();
            $id = $this->db->lastInsertId("ReportID");
        } 
        catch (PDOException $e) {
            //$this->logger->log("User::save_incident_report(): DB update failed: " . $e->getMessage()); 
        }
        catch (ErrorException $e) {
            //$this->logger->log("User::save_incident_report(): DB update failed: " . $e->getMessage()); 
        }

        // If successful, return the ID of the db insert.  If not, $returnValue will be false.
        if($success === true && ctype_digit($id)) {

            $returnValue = $id;
        }

        return $returnValue;
    }

    /**
     * Retrieve array of all incident reports   
     *                                                     
     * @return array  array of all incident reports; bool  false if transaction can not be completed  
     *        
     */
    public function get_report_data_array() {

        $data = false;

        $id = "ReportID";
        $stmt = $this->db->prepare("SELECT * FROM reports ORDER BY $id DESC");
        try {

            $stmt->execute();
            $data = $stmt->fetchAll();
        }
        catch (PDOException $e) {
            //$this->logger->log("User::get_report_data_array(): DB update failed: " . $e->getMessage()); 
        }
        catch (ErrorException $e) {
            //$this->logger->log("User::get_report_data_array(): DB update failed: " . $e->getMessage()); 
        }

        return $data;
    }

    /**
     * Retrieve array of all users and user data
     *                                                     
     * @return array  array of user data; bool  false if transaction can not be completed  
     *        
     */
    public function get_user_data_array() {

        $data = false;

        $stmt = $this->db->prepare("SELECT * FROM emails");
        try {

            $stmt->execute();
            $data = $stmt->fetchAll();
        }
        catch (PDOException $e) {
            //$this->logger->log("User::get_user_data_array(): DB update failed: " . $e->getMessage()); 
        }
        catch (ErrorException $e) {
            //$this->logger->log("User::get_user_data_array(): DB update failed: " . $e->getMessage()); 
        }

        return $data;
    }


    /**
     * 
     *                                                     
     * @return bool  FALSE if transaction can not be completed, TRUE if successful
     *        
     */
    public function edit_user_data($updatedData) {

        $data = false;

        $email = $updatedData['userEmail'];
        $sendType = $updatedData['sendType'];
        $isAdmin = ($updatedData['admin'] == "Yes") ? 1 : 0;  // convert string to int
        $loginID = $updatedData['userName'];
        $emailID = $updatedData['userid'];

        $qString = "UPDATE emails SET Email='" . $email . "', SendType='" . $sendType . "', isAdmin='" . $isAdmin . "', DUID='" . $loginID . "' WHERE EmailID = '" . $emailID . "'";
        $stmt = $this->db->prepare($qString);
        try {

            $data = $stmt->execute();
        }
        catch (PDOException $e) {
            //$this->logger->log("User::get_user_data_array(): DB update failed: " . $e->getMessage());
        }
        catch (ErrorException $e) {
            //$this->logger->log("User::get_user_data_array(): DB update failed: " . $e->getMessage()); 
        }

        return $data;
    }

    /**
    * Send an email containing all report data to all 'emails' email recipients in the IR database
    * 
    * @param array $incidentReport      Data fields to be displayed in the email
    * @param int $trackingNumber    Tracking number to be displayed in the email 
    * @return bool                  TRUE if email sent successfully, FALSE if there is an error
    */
    public function send_email_notifications($incidentReport, $trackingNumber) {

        $statusList = array();

        $emailList = $this->email->get_email_list();

        $body = '<h2>*** Please do not respond to this email ***</h2>' . 
               '<strong>(INCIDENT REPORT):</strong>' . "<br />" . 
               '<strong>Tracking Number</strong>: '. $trackingNumber . "<br />" . 
               '<strong>Date of Report</strong>: '. $incidentReport['dateOfReport'] . "<br />" . 
               '<strong>Report Completed By</strong>: ' . $incidentReport['reportCompletedBy'] . "<br />" . 
               '<strong>Title</strong>: ' . $incidentReport['title'] . "<br />" . 
               '<strong>Department</strong>: ' . $incidentReport['department'] . "<br />" .
               '<strong>Extension</strong>: ' . $incidentReport['extension'] . "<br />" .
               '<strong>Date of Offense</strong>: ' . $incidentReport['dateOfOffense'] . "<br />" .
               '<strong>Time of Offense</strong>: ' . $incidentReport['timeOfOffense'] . "<br />" .
               '<strong>Was Campus Safety Notified</strong>: ' . $incidentReport['wasCampusSafetyNotified'] . "<br />" .
               '<strong>Nature of Offense</strong>:' . "<br />" .
               "&nbsp;&nbsp;" . $incidentReport['natureOfOffense'] . "<br />" .
               '<strong>Location of Offense</strong>: ' . "<br />" .
               "&nbsp;&nbsp;" . $incidentReport['locationOfOffense'] . "<br />" .
               '<strong>Offense Narrative</strong>: ' . "<br />" .
               "&nbsp;&nbsp;" . $incidentReport['offenseNarrative'] . "<br /><br />" .
               '<strong>(VICTIM INFORMATION):</strong>' . "<br />" .
               '<strong>Name</strong>: ' . $incidentReport['victimName'] . "<br />" .
               '<strong>Gender</strong>: ' . $incidentReport['victimGender'] . "<br />" .
               '<strong>Approximate Age</strong>: ' . $incidentReport['victimApproximateAge'] . "<br />" .
               '<strong>Race</strong>: ' . $incidentReport['victimRace'] . "<br />" . 
               '<strong>University Affiliation</strong>: ' . $incidentReport['victimUniversityAffiliation'] . "<br /><br />" .
               '<strong>(SUSPECT INFORMATION):</strong>' . "<br />" .
               '<strong>Name</strong>: ' . $incidentReport['suspectName'] . "<br />" .
               '<strong>Gender</strong>: ' . $incidentReport['suspectGender'] . "<br />" .
               '<strong>Approximate Age</strong>: ' . $incidentReport['suspectApproximateAge'] . "<br />" .
               '<strong>Race</strong>: ' . $incidentReport['suspectRace'] . "<br />" .
               '<strong>University Affiliation</strong>: ' . $incidentReport['suspectUniversityAffiliation'] . "<br /><br />" .
               '<strong>(DESCRIPTION OF SUSPECT):</strong>' . "<br />" .
               '<strong>Clothing</strong>: ' . $incidentReport['suspectClothing'] . "<br />" . 
               '<strong>Hair</strong>: ' . $incidentReport['suspectHair'] . "<br />" .
               '<strong>Approximate Height</strong>: ' . $incidentReport['suspectApproximateHeight'] . "<br />" .
               '<strong>Approximate Weight</strong>: ' . $incidentReport['suspectApproximateWeight'] . "<br />" .
               '<strong>Beard/Moustache</strong>: ' . $incidentReport['suspectFacialHair'] . "<br />" .
               '<strong>Glasses</strong>: ' . $incidentReport['suspectGlasses'] . "<br />" .
               '<strong>Other Physical Characteristics</strong>: ' . $incidentReport['otherPhysicalCharacteristics'] . "<br />" .
               '<h2>*** Please do not respond to this email ***</h2>';

        return $this->email->send_email($emailList,$body);
    }

    /**
     * 
     *                                                     
     * @return bool  FALSE if transaction can not be completed, TRUE if successful
     *        
     */
    public function delete_data($userID) {

        $success = false;

        $qString = "DELETE FROM emails WHERE EmailID='" . $userID . "'";
        $stmt = $this->db->prepare($qString);
        try {

            $success = $stmt->execute();
        }
        catch (PDOException $e) {
            //$this->logger->log("User::get_user_data_array(): DB update failed: " . $e->getMessage());
        }
        catch (ErrorException $e) {
            //$this->logger->log("User::get_user_data_array(): DB update failed: " . $e->getMessage()); 
        }

        return $success;
    }

    /**
     * @param array $data   User data. Elements: Email, SendType, isAdmin, LoginID
     *                                                     
     * @return number  0 if transaction can not be completed, emailID of new user if successful     
     */
    public function add_user_data($data) {

        $id = 0;

        $email = $data['Email'];
        $sendType = $data['SendType'];
        $isAdmin = ($data['isAdmin'] == "Yes") ? 1 : 0;  // convert string to int
        $loginID = $data['LoginID'];

        $qString = "INSERT INTO emails (Email, SendType, isAdmin, DUID) VALUES ('" . $email . "', '" . $sendType . "', '" . $isAdmin . "', '" . $loginID . "')";
        $stmt = $this->db->prepare($qString);
        try {

            $success = $stmt->execute();
            $id = $this->db->lastInsertId();
        }
        catch (PDOException $e) {
            //$this->logger->log("User::get_user_data_array(): DB update failed: " . $e->getMessage());
        }
        catch (ErrorException $e) {
            //$this->logger->log("User::get_user_data_array(): DB update failed: " . $e->getMessage()); 
        }

        return $id;
    }


    /**
     * Retrieves one report from the database
     * 
     * @param string $ReportID                                                       
     *
     * @return array Report data IF successful; bool FALSE IF unsuccessful
     */
    public function get_report_by_id($reportID) {

        $results = false;

        $qString = "SELECT * FROM reports WHERE ReportID='" . $reportID . "'";
        $stmt = $this->db->prepare($qString);
        try {

            if($stmt->execute()) {

                $results = $stmt->fetchAll();
            }
        }
        catch (PDOException $e) {
            //$this->logger->log("User::get_report_by_id(): DB error: " . $e->getMessage());
        }
        catch (ErrorException $e) {
            //$this->logger->log("User::get_report_by_id(): DB error: " . $e->getMessage()); 
        }

        return $results;
    }

    /**
     * Searches NatureOfOffense field data
     * Restricts results to a date range
     * 
     * @param string $term  The 'needle'
     * @param string $fromDate  From oldest date if null  yyyy-mm-dd
     * @param string $toDate    To newest date if null   yyyy-mm-dd                                                 
     *
     * @return array Search result reports IF successful; bool FALSE IF unsuccessful
     */
    public function search_reports($term = "", $fromDate = null, $toDate = null) {

        // Default return value
        $results = false;

        // Construct query string, add date range restriction if present
        $qString = "SELECT * FROM reports WHERE NatureOfOffense LIKE '%" . $term . "%'";
        if($fromDate != null) {
             $qString .= " AND DateOfReport >= '" . $fromDate . "'";
        }
        if($toDate != null) {
             $qString .= " AND DateOfReport <= '" . $toDate . "'";
        }
        $qString .= "ORDER BY ReportID DESC";

        // Run search, append message if an empty result set is returned.
        $stmt = $this->db->prepare($qString);
        try {

            if($stmt->execute()) {

                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if(empty($results)) {
                    $results["MESSAGE"] = "No results found.";
                }
            }
        }
        catch (PDOException $e) {
            // DEBUG:
            //$results["MESSAGE"] = "PDOException: " . $e->getMessage() . " QSTRING: " . $qString;
        }
        catch (ErrorException $e) {
            // DEBUG:
            //$results["MESSAGE"] = "ErrorException: " . $e->getMessage() . " QSTRING: " . $qString;
        }

        return $results;
    }

    /**
     * Update an existing report
     * 
     * @param string $id  Id of report to update
     * @param array $report  Report data to update.  Array elements must match database fields, in total count, and in order of appearance                                                 
     *
     * @return bool FALSE IF unsuccessful, TRUE if successful
     */
    public function update_report($id,$report) {

        $success = false;

        // Bind report data to statement.  
        $qString = "UPDATE reports SET VictimName=?, VictimGender=?, VictimApproximateAge=?, VictimRace=?, VictimUniversityAffiliation=?, SuspectName=?, 
                    SuspectGender=?, SuspectApproximateAge=?, SuspectRace=?, SuspectUniversityAffiliation=?, SuspectClothing=?, SuspectHair=?, SuspectApproximateHeight=?, 
                    SuspectApproximateWeight=?, SuspectFacialHair=?, SuspectGlasses=?, OtherPhysicalCharacteristics=? WHERE ReportID='" . $id . "'";

        $stmt = $this->db->prepare($qString);
        $count = 1;

        foreach($report as $key => $value) {

            // if($count == 1 || $count == 6) {   // First database field to write, 

            //     // convert date strings to date
            //     $time = strtotime($value);
            //     $value = date("Y-m-d", $time);
            // }
            $stmt->bindParam($count, trim($value));
            $count++;
        }
        try {

            $success = $stmt->execute();
            //$id = $this->db->lastInsertId("ReportID");
        } 
        catch (PDOException $e) {
            //$this->logger->log("User::update_report(): DB update failed: " . $e->getMessage()); 
        }
        catch (ErrorException $e) {
            //$this->logger->log("User::update_report(): DB update failed: " . $e->getMessage()); 
        }

        //if($success !== true) {$success['MESSAGE'] = print_r($stmt,1);}
        return $success;
    }

    /**
     * Return a 2D array of column data, listed under the requested fields.
     * If 'field1' is included in the array, the returned array will contain all unique rows under the column 'field1'
     * 
     * @param array $fields  Array of DB column names (as strings) to get all data from.                                              
     *
     * @return bool FALSE IF unsuccessful, array of matching DB rows under specified $field.
     */
    public function get_field_data($fields) {

        $returnData = false;
        $alldata = array();

        foreach($fields as $field) {

            $sql = "SELECT " . $field . " FROM reports GROUP BY " . $field;
            //$sql = "SELECT * FROM reports";
            $stmt = $this->db->prepare($sql);
            try {
                $stmt->execute();
                $data = $stmt->fetchAll(PDO::FETCH_NUM);
            }
            catch (PDOException $e) {
                //$this->logger->log("User::get_auto_suggest_results(): DB error: " . $e->getMessage()); 
                continue;
            }
            catch (ErrorException $e) {
                //$this->logger->log("User::get_auto_suggest_results(): DB error: " . $e->getMessage()); 
                continue;
            }

            // main 2D array
            $alldata[$field] = $data;
        }

        // Parse the result data into a two dimensional array
        if($alldata !== false && empty($alldata) === false) {

            $temp = array();
            foreach($alldata as $dataKey => $data) {
                if(sizeof($data) > 1) {
                    // Get all of the data listed 
                    foreach($data as $resultItem) {
                        $temp[$dataKey][] = $resultItem[0];
                    }
                }
                // Single result
                else {
                    $temp[$dataKey] = $data[0];
                }
            }
            $returnData = $temp;
        }

        return $returnData;
    }
}


