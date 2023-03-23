<?php

/**
 * Incident Reports App
 *
 * Email model class
 * PHP version 5
 *
 * LICENSE: 
 *
 * @category   IncidentReports Email Service Management
 * @package    
 * @author     Jeff Rynhart <jeff.rynhart@du.edu>
 * @copyright  2015 University of Denver
 */

//require_once 'app/libs/mySQL.php';
require 'app/libs/phpmailer/phpmailer/PHPMailerAutoload.php';
require 'SimpleLogger.php'

class Email
{
    public $app;
    private $db;
    private $mail;
    private $logger;

    /**
     * Constructor
     *
     * @param Slim instance $app 
     */
    function __construct($app) {

        $this->app = $app;
        $this->db = connectToDB();
        $this->mail = new PHPMailer;
	$this->logger = new SimpleLogger();
    }

    /**
     * Get a list of all email addresses that require a new report notification
     *
     * @return array    Array of all email addresses and send types; bool   false if error occurs
     */
    public function get_email_list() {

        $data = false;

        $stmt = $this->db->prepare("SELECT Email, SendType FROM emails");   // TODO: append "WHERE isAdmin = '1'" if required
        try {

            $stmt->execute();
            $responseData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convert response array to IR format of ['email address' => 'SendType']
            for($i=0; $i < sizeof($responseData); $i++) {

                $data[ $responseData[$i]['Email'] ] = $responseData[$i]['SendType'];
            }
        }
        catch (PDOException $e) {
            //$this->logger->log("User::save_incident_report(): DB update failed: " . $e->getMessage()); 
        }
        catch (ErrorException $e) {
            //$this->logger->log("User::save_incident_report(): DB update failed: " . $e->getMessage()); 
        }

        return $data;
    }

    /**
     * Sends an email to the specified recipient
     *
     * @param array     $list Array of email recipients in format ['email address' => 'sendType']
     * @param string    $body Body of email message 
     * @return bool    true if email was sent successfully, false if not
     */
    public function send_email($list, $body) {

        $status = false;

        $this->mail->isSMTP();                                      // Set mailer to use SMTP
        $this->mail->Host = 'mailout.du.edu';                       // Specify main and backup SMTP servers
        //$this->mail->SMTPAuth = true;                               // Enable SMTP authentication
        $this->mail->Port = 25;                                     // TCP port to connect to

        $this->mail->From = 'intrazone@du.edu';
        $this->mail->FromName = 'Incident Report';

        foreach($list as $key => $value) {
		$this->logger->log("MAILTO ADDR: " . $key);
            if($value == "to") {

                $this->mail->addAddress($key);
            }
            else if($value == "cc") {

                $this->mail->addCC($key);
            }
            else if($value == "bcc") {

                $this->mail->addBCC($key);
            }
        }

        $this->mail->isHTML(true);                                  // Set email format to HTML

        $this->mail->Subject = 'New Incident Report';
        $this->mail->Body    = $body;

        // Send it
        if($this->mail->send()) {
            
            $status = true;
        }

        return $status;
    }
}
