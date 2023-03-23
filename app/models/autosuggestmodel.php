<?php

/**
 * Copyright 2010 University of Denver--Penrose Library
 * Author fernando.reyes@du.edu
 * 
 * This file is part of IncidentReports.
 * 
 * IncidentReports is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * IncidentReports is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with IncidentReports.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 *
 * Updated for non-Codeigniter use with PHP PDO database driver. 9-2015 University of Denver
 * This Class now utilizes php PDO library.
 *
 *
 **/

	class AutoSuggestModel {

		private $db;

		public function __construct($dbHandle) {
			$this->db = $dbHandle;
 		}
		
 		/**
 		 *  gets names in report completed by
 		 */
 		public function getReportCompletedBy( $keyword ) {
 			$field = "ReportCompletedBy";
 			$query = $this->doAutoSuggest( $field, $keyword );
 			return $this->parseResults($field, $query); 		
 		}
 		 		
 		
		/**
 		 * gets titles
 		 */
 		public function getTitle( $keyword ) {
 			$field = "Title";
 			$query = $this->doAutoSuggest( $field, $keyword );
 			return $this->parseResults($field, $query); 
 		}
 		
		/**
 		* gets departments
 		*/
 		public function getDepartment( $keyword ) {
 			$field = "Department";
 			$query = $this->doAutoSuggest( $field, $keyword );
 			return $this->parseResults($field, $query);
 		}
 		
 		/**
 		* gets extensions
 		*/
 		public function getExtension( $keyword ) {
 			$field = "Extension";
 			$query = $this->doAutoSuggest( $field, $keyword );
 			return $this->parseResults($field, $query);
 		}
 		
		/**
 		* gets nature of offense
 		*/
 		public function getNatureOfOffense( $keyword ) {
 			$field = "NatureOfOffense";
 			$query = $this->doAutoSuggest( $field, $keyword );
 			return $this->parseResults($field, $query);
 		}
 		
 		/**
 		* gets victim genders
 		*/
 		public function getVictimGender( $keyword ) {
 			$field = "VictimGender";
 			$query = $this->doAutoSuggest( $field, $keyword );
	 		return $this->parseResults($field, $query);
 		}
 		
		/**
 		* gets victim race
 		*/
 		public function getVictimRace( $keyword ) {
 			$field = "VictimRace";
 			$query = $this->doAutoSuggest( $field, $keyword );
	 		return $this->parseResults($field, $query);
 		}
 		
		/**
 		* gets suspect genders
 		*/
 		public function getSuspectGender( $keyword ) {
 			$field = "SuspectGender";
 			$query = $this->doAutoSuggest( $field, $keyword );
	 		return $this->parseResults($field, $query);
 		}
 		
		/**
 		* gets suspect race
 		*/
 		public function getSuspectRace( $keyword ) {
 			$field = "SuspectRace";
 			$query = $this->doAutoSuggest( $field, $keyword );
 			return $this->parseResults($field, $query);
 		}
 		
		/**
 		* gets suspect university affiliation
 		*/
 		public function getSuspectUniversityAffiliation( $keyword ) {
 			$field = "SuspectUniversityAffiliation";
 			$query = $this->doAutoSuggest( $field, $keyword );
	 		return $this->parseResults($field, $query);
 		}
 		
		/**
 		* gets victim university affiliation
 		*/
 		public function getVictimUniversityAffiliation( $keyword ) {
 			$field = "VictimUniversityAffiliation";
 			$query = $this->doAutoSuggest( $field, $keyword );
	 		return $this->parseResults($field, $query);
 		}
 		
 		/**
 		 * performs autosuggest query
 		 * @param string $field
 		 */
 		private function doAutoSuggest($field, $keyword) {	

 			$data = null;
 			$sql = "SELECT " . $field . " FROM reports WHERE " . $field . " LIKE '%" . $keyword . "%' GROUP BY " . $field;
 			//$sql = "SELECT * FROM reports";
 			$stmt = $this->db->prepare($sql);
	        try {

	            $stmt->execute();
	            $data = $stmt->fetchAll(PDO::FETCH_NUM);
	        }
	        catch (PDOException $e) {
	            //$this->logger->log("User::doAutoSuggest(): DB error: " . $e->getMessage()); 
	        }
	        catch (ErrorException $e) {
	            //$this->logger->log("User::doAutoSuggest(): DB error: " . $e->getMessage()); 
	        }

	        return $data;
 		}
 		
		/**
 		 * parses results out of query object and places them into array
 		 * @param string $field
 		 * @param array $query
 		 */
 		private function parseResults($field, $query) {
 			
 			$queryResultArray = array();
			foreach($query as $result) {

				if(sizeof($result) > 1) {

					foreach($result as $resultItem) {
						$queryResultArray[] = $resultItem;
					}
				}
				else {
					$queryResultArray[] = $result[0];
				}
			}
			return $queryResultArray;
 		}
	}