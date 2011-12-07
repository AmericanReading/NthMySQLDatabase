<?php

    class NthMySQLResultSet implements Iterator {

        private $result;		//Stores the MySQLi result resource.
        private $position;		//The currently selected row in the result set.
        private $numResults;	//How many results were returned?
        private $checkSum;		//An MD5 checksum of the SQL query used to generate this result set.

        /**
        * Instantiate a new NthMySQLResultSet given an MySQLi result resource.
        * 
        * @param mixed $result The result resource from MySQLi
        * @param string $sqlQuery The SQL query used to generate the result set.
        * @return NthMySQLResultSet
        */
        public function __construct(&$result, $sqlQuery) {
            $this->result =& $result;
            $this->numResults = $this->result->num_rows;
            $this->position = 0;
            $this->checkSum = md5($sqlQuery);
        }

        /**
        * When the NthMySQLResultSet object is destroyed, make sure to free the result resource.
        * 
        */
        public function __destruct() {
            $this->result->free();
        }

        /**
        * The current result position.
        * Supports the Iterator interface.
        * 
        */
        public function key() {
            return $this->position;
        }

        /**
        * The row stored at the current position, expressed as an associative array.
        * Supports the Iterator interface.
        * 
        */
        public function current() {
            $this->result->data_seek($this->position);
            return $this->result->fetch_assoc();
        }

        /**
        * Checks if the current position is valid.
        * Supports the Iterator interface.
        * 
        */
        public function valid() {
            return ($this->position < $this->numResults);
        }

        /**
        * Increments the current position.
        * Supports the Iterator interface.
        * 
        */
        public function next() {
            ++$this->position;
        }

        /**
        * Moves the position back to the start of the record set.
        * Supports the Iterator interface.
        * 
        */
        public function rewind() {
            $this->position = 0;
        }

        /**
        * Returns the number of records in this result set.
        * 
        */
        public function numResults() {
            return $this->numResults;
        }

        /**
        * Returns the value of the first field of the first row of this record set.
        * 
        */
        public function firstValue() {
            $this->result->data_seek(0);
            $data = $this->result->fetch_row();
            $this->result->data_seek($this->position);
            return $data[0];
        }

        /**
        * Returns the entire record set as an associative array.
        * This can consume a lot of memory if the record set is large.
        * 
        */
        public function assocArray() {
            //Rewind to the beginning of the result set.
            $this->result->data_seek(0);

            $a = array();

            while($r = $this->result->fetch_assoc()) { $a[] = $r; }

            return $a;
        }

        /**
        * Return the first row of the result set as an associative array.
        *
        */
        public function assocArraySingle() {
            //Rewind to the beginning of the result set.
            $this->result->data_seek(0);

            $r = $this->result->fetch_assoc();

            return $r;
        }

    }

?>
