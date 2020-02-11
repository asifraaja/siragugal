<?php
    class Database{
        private $host = "localhost";
        private $db_name = "siragugal";
        private $username = "root";
        private $password = "";
        private $conn;

        public function connect(){
            $this->conn = null;

            try{
                /*
                $this->conn = new PDO('mysql:host=' .$this->host. ';dbname=' .$this->db_name, $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                */
                $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
                // echo "Database connected";
            }catch(Exception $e){
                echo "Connection Problem ".$e."\n";
                // echo "Connection error while connecting DB" . $e->getMessage();
            }

            return $this->conn;
        }

        public function disconnect(){
            try{
                $this->conn->close();
                // echo "Database Disconnected";
            }catch(Exception $e){
                // echo "Connection error while closing DB" . $e->getMessage();
            }
        }
    }
?>