<?php

    class NthMySQLResultSet implements ArrayAccess, Countable, SeekableIterator {

        protected $result;		//Stores the MySQLi result resource.
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



        ////////////////////////////////////////////////////////////////////////////
        // !Implemented Abstract Methods from Interfaces
        // -------------------------------------------------------------------------
        // ArrayAccess
        // Countable
        // SeekableIterator 
        ////////////////////////////////////////////////////////////////////////////
        
        ////////////////////////////////////////////////////////////////////////////
        // !ArrayAccess methods
        // -------------------------------------------------------------------------
        // ::offsetExists()
        // ::offsetGet()
        // ::offsetSet()
        // ::offsetUnset()
        
        /**
         * Returns whether the passed offset is valid.
         * 
         * @param int $offset
         * @return bool
         */
        public function offsetExists($offset) {
        
            if (is_int($offset)) {  
                if ($offset >= 0 && $offset < $this->numResults) {
                    return true;
                } else {
                    throw new OutOfBoundsException('Index out of range');
                }
            } else {
                throw new InvalidArgumentException('Expected interger');
            }

        }
        
        /**
         * Fetches the result at the passed offset.
         *  
         * @param int $offset
         * @return array
         */
        public function offsetGet($offset) 
        {
            $this->seek($offset);
            return $this->fetch();
        }
    
        /**
         * Always throws an exception because the result is read only.    
         * 
         * @param mixed $offset
         * @param mixed $value
         */
        public function offsetSet($offset, $value) 
        {
            throw new Exception('Cannot alter row. ResultSet rows are read only.');
        }
        
        /**
         * Always throws an exception because the result is read only.
         * 
         * @param mixed $offset
         */
        public function offsetUnset($offset) 
        {
            throw new Exception('Cannot unset row. ResultSet rows are read only.');
        }
        


        ////////////////////////////////////////////////////////////////////////
        // !Countable methods
        // ---------------------------------------------------------------------
        // ::count()
        
        /**
         * Returns the number of rows. Enables count($instance).
         * 
         * @return int
         */
        public function count() {
            return $this->numResults();
        }
        
        
        
        ////////////////////////////////////////////////////////////////////////////
        // !SeekableIterator methods 
        // -------------------------------------------------------------------------
        // ::seek()
        // ::rewind()
        // ::current()
        // ::key()
        // ::next()
        // ::valid() 
        
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
            return $this->fetch();
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
         * Advances the pointer to the passed offset.
         * 
         * @param int $offset
         */
        public function seek($offset) {
        
            if ($this->offsetExists($offset)) {
                $this->position = $offset;
                $this->result->data_seek($offset);    
            }
            
        }


        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        
        /**
         * Fetch and return the current row. Because of how the result class is
         * implemented, this advances the result object's pointer.
         * 
         * To use a differenct fetch method (e.g., as objects), subclass RecordSet
         * and MySQLdb and redefine this function.
         * 
         * @return array
         */
        public function fetch() {
            return $this->result->fetch_array(MYSQL_ASSOC);
        }
        
        /**
         * Fetch all records and return an associative arra.
         * 
         * @return array
         */
        public function fetchAll() {
        
            $arr = array();
            
            foreach ($this as $v) {
                $arr[] = $v;
            }
            
            return $arr;    
            
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
