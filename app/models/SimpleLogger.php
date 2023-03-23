<?php

/*
 * Simple php logger class
 * 
 * Usage: 
 *
 *   $logger = new SimpleLogger($pathToLogFile) *** include any leading and trailingslash
 *
 *   Will output data to file specified in $pathToLogFile 
 *   
 *   All logs are appended to the current logfile.
 */

class SimpleLogger {

	private $filePtr;

	function __construct($pathToLogFile = "") {

    try {
        $logfile = $pathToLogFile . date("Y-m-d") . "_log.txt";
        $this->filePtr = fopen($logfile, "a");	
        if ( !$this->filePtr ) {
          throw new Exception('File open failed.');
        }
    }
    catch ( Exception $e ) {
       $this->filePtr = fopen("logfile.txt", "a"); // If the specified folder or file can not be opened, place a default logfile in the current folder
    } 
  }

   /*
    * Create an entry in the logfile
    *
    * @param string $text     The text string to write to the log file
    * @param string $header   Text of the log entry header tag. Can be any string, or use presets (d='DEBUG', i='INFO', e="ERROR")
    */
   function log($text,$header = "d") {
   		$date = date(DATE_ATOM);

   		// Set the header tag
      switch($header) {

   			case "i":
   				$headerText = "INFO";
   				break;
   			case "d": 
   				$headerText = "DEBUG";
   				break;
   			case "e":
   			    $headerText = "ERROR";
   			    break;
   			default: 
   				$headerText = $header;
   				break;
   		}

   		// Construct the log entry and write to the logfile
      $logLine = $date . " [ " . $headerText . " ]: " . $text . "\n"; 
   		$this->writeToFile($logLine);
   }

   function writeToFile($logText) {
      fwrite($this->filePtr, $logText);
   }

   function close() {
   		fclose($this->filePtr);
   }
}

?>