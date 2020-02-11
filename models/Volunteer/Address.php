<?php
    include_once '../../config/Properties.php';
    include_once '../../config/Validator.php';

    class Address{

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
         * Update the Address
         * IF the user has already saved the data and clicks on save & continue.
         */
        public function updateAddress($userId, $nativeState, $nativeDistrict, $nativeRegion,  
                            $permanentAddress, $permanentDistrict, $permanentState, $permanentPincode, 
                            $currentAddress, $currentDistrict, $currentState, $currentPincode){
            $res = array();
            // 1. Delete the existing Address
            $query = "DELETE FROM vol_address WHERE usr_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('d', $userId);
            $delete_contact_result = $stmt->execute();
            if($delete_contact_result == false) return self::setAddressError('DELETE_ADDRESS_ERROR');
            // 2. Add the new Address
            $res = self::addAddress($userId, $nativeState, $nativeDistrict, $nativeRegion,  
                                $permanentAddress, $permanentDistrict, $permanentState, $permanentPincode, 
                                $currentAddress, $currentDistrict, $currentState, $currentPincode);
            return $res;
        }

        /**
         * Add the address details into vol_addresss table and return the response
         */
        public function addAddress($userId, $nativeState, $nativeDistrict, $nativeRegion,  
                                    $permanentAddress, $permanentDistrict, $permanentState, $permanentPincode, 
                                    $currentAddress, $currentDistrict, $currentState, $currentPincode){
            $res = array();
            // 1. Add a new Address
            $query = "INSERT INTO vol_address (usr_id, native_state, native_district, native_region,".
            " permanent_address, permanent_district, permanent_state, permanent_pincode,".
            " curr_address, curr_district, curr_state, curr_pincode) VALUES(?,?,?,?, ?,?,?,?, ?,?,?,?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('dssssssdsssd', $userId, $nativeState, $nativeDistrict, $nativeRegion, $permanentAddress, $permanentDistrict, $permanentState, $permanentPincode, $currentAddress, $currentDistrict, $currentState, $currentPincode);
            $insert_contact_result = $stmt->execute();
            if($insert_contact_result == false) return self::setAddressError('INSERT_ADDRESS_ERROR');
            $res['error'] = '0';
            $res['errorCode'] = '';
            $res['errorMessage'] = '';
            $res['address'] = self::setAddressInfo($userId, $nativeState, $nativeDistrict, $nativeRegion, 
                                        $permanentAddress, $permanentDistrict, $permanentState, $permanentPincode, 
                                        $currentAddress, $currentDistrict, $currentState, $currentPincode);
            return $res;
        }

        /**
         * Set the error code & error details
         */
        public function setAddressError($errorCode){
            $res = array();
            $res['error'] = '-1';
            $res['errorCode'] = $errorCode;
            $res['errorMessage'] = 'Something went wrong while adding Address Details. We\'ll fix it ASAP';
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
         * Return the address information for a particular userId
         */
        public function getAddressInfo($userId){
            $query = "SELECT * FROM vol_address WHERE usr_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('d', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            $res = self::setAddressInfo($userId, $row['native_state'], $row['native_district'], $row['native_region'], 
                        $row['permanent_address'], $row['permanent_district'], $row['permanent_state'], $row['permanent_pincode'],
                        $row['curr_address'], $row['curr_district'], $row['curr_state'], $row['curr_pincode']);
            $res['addressId'] = $row['vol_addr_id'];
            return $res;
        }

        /**
         * Setting Address Information
         */
        private function setAddressInfo($userId, $nativeState, $nativeDistrict, $nativeRegion,  
                                    $permanentAddress, $permanentDistrict, $permanentState, $permanentPincode, 
                                    $currentAddress, $currentDistrict, $currentState, $currentPincode){
            $res = array();
            $res['nativeState'] = $nativeState;
            $res['nativeDistrict'] = $nativeDistrict;
            $res['nativeRegion'] = $nativeRegion;

            $res['permanentAddress'] = $permanentAddress;
            $res['permanentDistrict'] = $permanentDistrict;
            $res['permanentState'] = $permanentState;
            $res['permanentPincode'] = $permanentPincode;

            $res['currentAddress'] = $currentAddress;
            $res['currentDistrict'] = $currentDistrict;
            $res['currentState'] = $currentState;
            $res['currentPincode'] = $currentPincode;

            return $res;
        }
    }
?>