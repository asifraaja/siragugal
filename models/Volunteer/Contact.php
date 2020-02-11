<?php
    include_once '../../config/Properties.php';
    include_once '../../config/Validator.php';

    class Contact{

        private $conn;
        private $props;
        /**
         * constructor for User
         * @param db - database connection
         */
        public function __construct($db){
            $this->conn = $db;
            $properties = new Properties();
            $this->props = $properties->getProps();
        }

        /**
         * Checks if a contact already exists for that phoneNumber
         * @param - phoneNumber
         * @return - returns userId if yes else returns -1
         */
        public function isContactAdded($userId){
            $query = "SELECT * FROM vol_contact WHERE usr_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('d', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows == 0) return -1;
            $row = $result->fetch_assoc();
            return $row['usr_id'];
        }

        /**
         * Update the contact. 
         * IF the user has already saved the data and clicks on save & continue.
         */
        public function updateContact($userId, $phoneNumber, $whatsappNumber, $emailId){
            $res = array();
            // 1. Delete the existing Contact
            $query = "DELETE FROM vol_contact WHERE usr_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('d', $userId);
            $delete_contact_result = $stmt->execute();
            if($delete_contact_result == false) return self::setContactError('DELETE_CONTACT_ERROR');
            // 2. Add the new Contact
            $res = self::addContact($userId, $phoneNumber, $whatsappNumber, $emailId);
            return $res;
        }

        public function addContact($userId, $phoneNumber, $whatsappNumber, $emailId){
            $res = array();
            // 1. Add a new contact
            $query = "INSERT INTO vol_contact(usr_id, mail_id, ph_no, whatsapp_no) VALUES(?,?,?,?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('dsss', $userId, $emailId, $phoneNumber, $whatsappNumber);
            $insert_contact_result = $stmt->execute();
            if($insert_contact_result == false) return self::setContactError('INSERT_CONTACT_ERROR');
            $res['error'] = '0';
            $res['errorCode'] = '';
            $res['errorMessage'] = '';
            $res['contactInfo'] = self::setContactInfo($emailId, $phoneNumber, $whatsappNumber);
            return $res;
        }

        public function setContactError($errorCode){
            $res = array();
            $res['error'] = '1';
            $res['errorCode'] = $errorCode;
            $res['errorMessage'] = 'Something went wrong while add contact. We\'ll fix it ASAP';
            return $res;
        }

        public function userExists($userId){
            $query = "SELECT * FROM user_details WHERE usr_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('d', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows == 0) return 0;
            $row = $result->fetch_assoc();
            return $row['usr_id'];
        }

        /**
         * Return the personal details for a particular userId
         */
        public function getContactInfo($userId){
            $query = "SELECT * FROM vol_contact WHERE usr_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('d', $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            $row = $result->fetch_assoc();
            $response = self::setContactInfo($row['mail_id'], $row['ph_no'], $row['whatsapp_no']);
            return $response;
        }

        /**
         * Setting Personal Information.
         */
        private function setContactInfo($emailId, $phoneNumber, $whatsappNumber){
            $response['emailId'] = $emailId;
            $response['phoneNumber'] = $phoneNumber;
            $response['whatsappNumber'] = $whatsappNumber;
            return $response;
        }
    }
?>