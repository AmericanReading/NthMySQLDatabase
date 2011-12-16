<?php

    /**
     * @package NthMySQLDatabase
     */
    
    class NthMySQLResultSet implements ArrayAccess, Countable, SeekableIterator {

        /**
         * @var object MySQLi result resource.
         */
        protected $result;
        
        /**
         * @var int The currently selected row in the result set
         */
        private $position;
        
        /**
         * @var int How many results were returned?
         */
        private $numResults;
        
        /**
         * @var string An MD5 checksum of the SQL query used to generate this result set.
         */
        private $checkSum;
        

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
         * When the NthMySQLResultSet object is destroyed, make sure to free
         * the result resource. 
         */
        public function __destruct() {
            $this->result->free();
        }



        ////////////////////////////////////////////////////////////////////////
        // !Implemented Abstract Methods from Interfaces
        // ---------------------------------------------------------------------
        // ArrayAccess
        // Countable
        // SeekableIterator 
        ////////////////////////////////////////////////////////////////////////
        
        ////////////////////////////////////////////////////////////////////////
        // !ArrayAccess methods
        // ---------------------------------------------------------------------
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
        public function offsetGet($offset) {
            
            // Seek to the passed position.
            $this->seekPointer($offset);
            
            // Fetch the data. This will advance the pointer.
            $data = $this->fetch();

            // Return to the original position.
            $this->seekPointer($this->position);
            
            // Return the first column.
            return $data;
            
        }
    
        /**
         * Always throws an exception because the result is read only.    
         * 
         * @param mixed $offset
         * @param mixed $value
         */
        public function offsetSet($offset, $value) {
            throw new Exception('Cannot alter row. ResultSet rows are read only.');
        }
        
        /**
         * Always throws an exception because the result is read only.
         * 
         * @param mixed $offset
         */
        public function offsetUnset($offset) {
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
            return $this->numResults;
        }
        
        
        
        ////////////////////////////////////////////////////////////////////////////
        // !SeekableIterator methods 
        // -------------------------------------------------------------------------
        // ::key()
        // ::current()
        // ::valid()
        // ::next()
        // ::rewind()
        // ::seek()
        
        /**
         * The current result position.
         * 
         * @return int
         */
        public function key() {
            return $this->position;
        }

        /**
         * Fetch the row at the current position.
         *
         * This method called the fetch() method, so overriding fetch() in a
         * subclass will change functionality of this method as well.
         *
         * @return array
         */
        public function current() {
            $this->seekPointer($this->position);
            return $this->fetch();
        }

        /**
         * Checks if the current position is valid.
         * 
         * @return bool
         */
        public function valid() {
            return ($this->position < $this->numResults);
        }
        
        /**
         * Increments the current position.
         */
        public function next() {
            ++$this->position;
        }

        /**
         * Moves the position back to the start of the record set.
         * Supports the Iterator interface.
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
            $this->position = $offset;
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
         * Fetch all records and return an array.
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
         * Returns the value of the first field of the first row.
         * 
         * @return string
         */
        public function fetchFirstValue() {
        
            // Seek to the first position.
            $this->seekPointer(0);
            
            // Fetch the data. This will advance the pointer.
            $data = $this->result->fetch_row();
            
            // Return to the original position.
            $this->seek($this->position);
            
            // Return the first column.
            return $data[0];

        }
        
        /**
         * Seek the result's pointer to the given offset.
         */
        protected function seekPointer($offset) {
            $this->result->data_seek($offset);
        }
    
    }

?>
