<?php
    include_once '../../config/Properties.php';
    include_once '../../config/Validator.php';

    class PersonalInfo{

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

        public function updateDOB($userId, $dob){
            $query = "UPDATE user_details SET dob = ? WHERE usr_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('sd', $dob, $userId);
            $update_dob_result = $stmt->execute();
        }

        /**
         * Update the Personal Information. 
         * IF the user has already saved the data and clicks on save & continue.
         */
        public function updatePersonalInfo($userId, $gender, $bloodGroup, $fatherName, $motherName, $dob){
            self::updateDOB($userId, $dob);
            $res = array();
            // 1. Delete the existing PersonalInformation
            $query = "DELETE FROM vol_personal_info WHERE usr_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('d', $userId);
            $delete_contact_result = $stmt->execute();
            if($delete_contact_result == false) return self::setPIError('DELETE_PERSONAL_INFO_ERROR');
            // 2. Add the new PersonalInformation
            $res = self::addPersonalInfo($userId, $gender, $bloodGroup, $fatherName, $motherName, $dob);
            return $res;
        }

        /**
         * Add the personal details into the vol_personal_info table and return the response
         */
        public function addPersonalInfo($userId, $gender, $bloodGroup, $fatherName, $motherName, $dob){
            $res = array();
            // 1. Add a new PersonalInformation
            $query = "INSERT INTO vol_personal_info(usr_id, gender, blood_group, father_name, mother_name) VALUES(?,?,?,?,?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('dssss', $userId, $gender, $bloodGroup, $fatherName, $motherName);
            $insert_contact_result = $stmt->execute();
            if($insert_contact_result == false) return self::setPIError('INSERT_PERSONAL_INFO_ERROR');
            $res['error'] = '0';
            $res['errorCode'] = '';
            $res['errorMessage'] = '';
            $res['personalInfo'] = self::setPersonalInfo($userId, $gender, $bloodGroup, $fatherName, $motherName, $dob);
            return $res;
        }

        /**
         * Set the error code & error details
         */
        public function setPIError($errorCode){
            $res = array();
            $res['error'] = '-1';
            $res['errorCode'] = $errorCode;
            $res['errorMessage'] = 'Something went wrong while adding Personal Information. We\'ll fix it ASAP';
            return $res;
        }

        /**
         * Checks if the user is already registered.
         */
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
        public function getPersonalInfo($userId){
            $query = "SELECT * FROM vol_personal_info v, user_details u WHERE u.usr_id = v.usr_id and v.usr_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('d', $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            $row = $result->fetch_assoc();
            $response['personalInfoId'] = $row['vol_pi_id'];
            $response['gender'] = $row['gender'];
            $response['bloodGroup'] = $row['blood_group'];
            $response['fatherName'] = $row['father_name'];
            $response['motherName'] = $row['mother_name'];
            $response['dob'] = $row['dob'];
            $response['name'] = $row['first_name']." ".$row['last_name'];
            return $response;
        }

        /**
         * Setting Personal Information.
         */
        private function setPersonalInfo($userId, $gender, $bloodGroup, $fatherName, $motherName, $dob){
            $response['gender'] = $gender;
            $response['bloodGroup'] = $bloodGroup;
            $response['fatherName'] = $fatherName;
            $response['motherName'] = $motherName;
            $response['dob'] = $dob;
            return $response;
        }
    }
?>