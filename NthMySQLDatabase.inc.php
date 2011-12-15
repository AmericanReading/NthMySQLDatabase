<?php

    require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'NthMySQLResultSet.inc.php');
    
    class NthMySQLConnectException extends Exception {
    	public function errorMessage() {
    		$e = "There was an error connecting to the database: " . $this->getMessage();
    		return $e;
		}	
	}

	class NthMySQLDatabase extends mysqli {
		
		protected $databaseErrors;		//An array of database errors since this connection was opened.
										//array( 0 => 'databaseError', ...)

		/**
		* Connects to an MySQL database.
		* 
		* @param mixed $dbConnectionArray
		* @return NthMySQLDatabase
		*/
		public function __construct($host, $username=null, $password=null,
                $dbname=null, $port=null, $socket=null) {
		
			//Call the parent mysqli constructor.
            parent::__construct($host, $username, $password, $dbname, $port, $socket);

            //Check to see if there was an error connecting.
            if ($this->connect_errno) {
                
                //Throw an exception if there was a connection error.
                throw new NthMySQLConnectException($this->connect_error);
                
            }
            
            $this->databaseErrors = array();
		}
		
		/**
		* Add an error message to the array of database errors.
		* 
		* @param string $sqlQuery The SQL query that resulted in an error.
		* @param string $errorMessage The resulting error message.
		*/
		protected function addDatabaseError($sqlQuery, $errorMessage) {
			$this->databaseErrors[] = $errorMessage;
		}
		
		/**
		* Return the array of database error messages.
		* 
		*/
		public function databaseErrors() {
			return $this->databaseErrors;	
		}
		
		/**
		* How many database errors were logged?
		* 
		*/
		public function numDatabaseErrors() {
			return count($this->databaseErrors);	
		}

        /**
        * Performs the SQL query and returns an NthMySQLResultSet.
        *
        * @param string $sqlQuery
        * @return NthMySQLResultSet
        */
		public function query($sqlQuery) {
			//If the query failed, log the error.
            if(($result=parent::query($sqlQuery))===false) {
            	$this->addDatabaseError($sqlQuery, $this->error);
            	return false;	
			} else {
				return $this->makeResultSet($result, $sqlQuery);	
			}
        }
        
        /**
        * Used to query stored procedures that return a value.
        *
        * @param string $sqlQuery
        * @return NthMySQLResultSet
        */
		public function querySP($sqlQuery) {
			//If the query failed, log the error.
            if(parent::multi_query($sqlQuery)===false) {
            	$this->addDatabaseError($sqlQuery, $this->error);
            	return false;	
			} else {
				//Store the first result, the one with our data.
				$result = parent::store_result();
				
				//Skip subsequent results.
				if(parent::more_results()) {
					while(parent::next_result());	
				}
				
				return $this->makeResultSet($result, $sqlQuery);	
			}
        }

        /**
        * Executes a SQL query that will produce no output.
        * 
        * @param string $sqlQuery
        * @return mixed
        */
        public function exec($sqlQuery) {
            //If the query fails, log the error.
            if(parent::query($sqlQuery)) {
            	return true;	
			} else {
				$this->addDatabaseError($sqlQuery, $this->error);
				return false;
			}
        }

        /**
        * Returns the value of the autoincrement column last affected by
        * an INSERT statement.
        * 
        */
		public function lastInsertId() {
            /**
            * Why not just use the mysql_insert_id() PHP function? That function does not
            * handle auto_increment columns with a type of BIGINT. This method ensures
            * a correct return value every time.
            */
            $lastInsertId = 0;
            $q = "SELECT LAST_INSERT_ID() as lastInsertId;";
            if($r = $this->query($q)) { $lastInsertId = $r->firstValue(); }
            return $lastInsertId;
        }
                 
        /**
         * Return a wrapped instance of the result.
         *
         * @param object $result  A MySQLi_Result
         * @param object $sqlQuery  The query used to obtain $result
         * @return mixed  The wrapped result
         */ 
        protected function makeResultSet($result, $sqlQuery) {
            return new NthMySQLResultSet($result, $sqlQuery);	
        }

	}

?>
