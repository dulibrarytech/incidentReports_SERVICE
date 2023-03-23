<?php

/**
 * Incident Reports App
 *
 * Session model class
 * PHP version 5
 *
 * LICENSE: 
 *
 * @category   User Session Management
 * @package    
 * @author     Jeff Rynhart <jeff.rynhart@du.edu>
 * @copyright  2015 University of Denver
 */

require_once 'app/libs/JWT/JWT.php';
//require_once 'app/libs/mySQL.php';

use Guzzle\Http\Client;

class Session
{
    private $app;
    private $tokenLife;
    private $tokenKey;
    private $db;

    /**
     * Constructor
     *
     * @param Slim instance $app 
     */
    function __construct($app) {

    	$this->app = $app;
        $this->tokenLife = 2700;
        $this->tokenKey = 'a8d7f9a5-d6d7-c743af7fd8';
        $this->db = connectToDB();
        $this->logger = new SimpleLogger();
    }

    public function validate_token_string($token = "") {
        $data = false;
        try {
            $data = JWT::decode($token, $this->tokenKey);
        }
        catch(UnexpectedValueException $e) {
            // log
        } 
        return $data;
    }

    /**
     * Validates token, or the token from the current request header
     * 
     * @param   string  JWT
     * @return  string  JWT with current timestamp (if current request token is valid and not expired), 
     *          bool    false (if token is invalid)  
     */
    public function validate_token($token = null) {

    	$auth = false;
        $tokenData = array();

        if($token == null) {

            $header = get_access_header();
            try {
                $tokenData = JWT::decode($header, $this->tokenKey);
            }
            catch(UnexpectedValueException $e) {
                // log
            }
        }
        else {

            try {
                $tokenData = JWT::decode($token, $this->tokenKey);
            }
            catch(UnexpectedValueException $e) {
                // log
            } 
        }

        // Verify token is a valid IR token and is not expired
        // TODO: Try to catch JWT DomainException
        if(isset($tokenData->timestamp)) {

            $curTime = time();
            $timestamp = intval($tokenData->timestamp);
            $elapsedLife = $curTime - $timestamp;
            if($elapsedLife < $this->tokenLife) {

                // Update time and encode JWT for return
                $tokenData->timestamp = strval($curTime);
                $auth = JWT::encode($tokenData, $this->tokenKey); 
            }
        }
        
    	return $auth;       
    }

    /**
     * Authenticate credentials with LDAP server
     *
     * @param string $username 
     * @param string $password
     * @return bool  true if username/password are autheticated by LDAP server. false if otherwise
     */
    public function ldap_authenticate($username,$password) {
        $auth = false;

        // Initialise the curl request
	$ch = curl_init('https://lib-appserver.du.edu/auth-service/api/v1/authenticate');
 
        // Build request object
        //curl_setopt($ch, CURLOPT_URL,"https://lib-appserver.du.edu/auth-service/api/v1/authenticate")
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            http_build_query(array(
              'username' => $username,
              'password' => $password,
            )));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Post credentials to auth-service
        $response =  curl_exec($ch); 
        $responseData = json_decode($response);

        if(isset($responseData->auth)) {
		$auth = $responseData->auth;
        }

        // Close the session
        curl_close($ch);
        
        return $auth;
    }

    /**
     * Authenticate username with IncidentReports database
     *
     * @param string $username  username
     * @return int   IR userID if login is verified as IR user. false if otherwise
     */
    public function ir_authenticate($login) {

        $auth = false;

        // Retrieve userID of username from DB.  If user is not in DB return false
        $qString = "SELECT EmailID, isAdmin FROM emails WHERE DUID='" . $login . "'";
        $stmt = $this->db->query($qString);

        try {

            $resultSet = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {

            $this->logger->log("Session::ir_authenticate(): DB select failed: " . $e->getMessage()); 
        }
        if(sizeof($resultSet) != 0 &&
            isset($resultSet[0]['EmailID']) !== false) {

            // Only admin can login 9-20-15
            if($resultSet[0]['isAdmin'] == 1) {
                $auth = $resultSet[0]['EmailID'];
            }
        }

        return $auth;
    }

    /**
     * Create token with userID and current timestamp
     *
     * @param string $userID 
     * @return string Encoded JWT  
     */
    public function create_user_token($userID, $userData) {

        $tokenArr = array();
        $tokenArr['id'] = $userID;
        $tokenArr['email'] = $userData['email'];
        $tokenArr['userID'] = $userData['userID'];
        $tokenArr['admin'] = $userData['admin'];
        $tokenArr['timestamp'] = time();

        return JWT::encode($tokenArr, $this->tokenKey);
    }

    /**
     * Return timestamp of encoded token
     *
     * @return string  timestamp
     */
    public function get_token_timestamp($token) {

        $arr = JWT::decode($token, $this->tokenKey);
        return $arr->timestamp;
    }

    /**
     * Retrieve user info and return in an array
     *
     * @param string  user ID
     * @return array  user profile array
     */
    public function get_user_profile($userID) {

        $userID = intval($userID);
        $qString = "SELECT * FROM emails WHERE EmailID='" . $userID . "'";      // *** Add exception handling
        $stmt = $this->db->query($qString);

        $resultSet = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $profile['email']       = $resultSet[0]['Email'];
        $profile['userID']      = $resultSet[0]['EmailID'];
        $profile['admin']       = $resultSet[0]['isAdmin'];

        return $profile;
    }
}
