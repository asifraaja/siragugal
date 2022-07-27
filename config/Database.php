<?php
    class Database{
        private $host = "localhost";
        private $db_name = "siragugal";
        private $username = "siragugal";
        private $password = "HbU1whFuM@6l";
        private $conn;

        public function connect(){
            $this->conn = null;

            try{
                $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
                // echo "Database connected";
            }catch(Exception $e){
                echo "Connection Problem ".$e."\n";
            }

            return $this->conn;
        }

        public function disconnect(){
            try{
                $this->conn->close();
                // echo "Database Disconnected";
            }catch(Exception $e){
                echo "Connection error while closing DB" . $e->getMessage();
            }
        }
    }
?>