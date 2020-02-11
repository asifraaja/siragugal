<?php
    class DatabaseUtility{
        protected static $_instance = null;
        protected $conn = null;
        protected $config = array(
            'username' => 'root',
            'password' => '',
            'hostname' => 'localhost',
            'database' => 'siragugal'
        );

        protected function __construct(){
           
        }

        public static function getInstance(){
            if(null === self::$_instance)
                self::$_instance = new self();
            return self::$_instance;
        }

        private function connect(){
            if(is_null($this->conn)){
                $this->conn = mysqli_connect($this->config['hostname'], $this->config['username'], $this->config['password'], $this->config['database']);
                if(!$this->conn) {
                    die("Cannot connect to database server"); 
                }
            }
        }

        private function reconnect(){
            if($this->conn == false){
                $this->connect();
            }
        }

        public function disconnect(){
            mysqli_close($this->conn);
        }

        public function selectQuery($query){
            $this->connect();
            $result = mysqli_query($this->conn, $query);
            if ($result->num_rows > 0) {
                return $result;
            } else {
                return null;
            }
            $this->disconnect();
        }

        public function executeQuery($query){
            $this->connect();
            if($this->conn->query($query) == TRUE){
                return true;
            }else{
                return false;
            }
            $this->disconnect();
        }
    }
?>