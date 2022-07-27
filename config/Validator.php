<?php
    class Validator{
        public function isValidUsername($string){
            return isset($string) && !empty($string) && self::isValidPhoneNumber($string);
        }

        public function isPasswordSet($string){
            return isset($string) && !empty($string);
        }

        public function isValidText($string){
            if(ctype_alpha($string))
                return true;
            return false;
        }

        public function isValidNumber($number){
            if(ctype_digit($number))
                return true;
            return false;
        }

        public function isValidEmail($email){
            if(filter_var($email, FILTER_VALIDATE_EMAIL))
                return true;
            return false;
        }

        public function isValidDate($date){
            
        }

        public function isValidPhoneNumber($phoneNumber){
            if(strlen($phoneNumber) == 10)
                return self::isValidNumber($phoneNumber);
            else
                return false;
        }

        public function isPassword($password){
            if(strlen($password) < 8){
                return false;
            }
            $containsLetter  = preg_match('/[a-zA-Z]/',$password);
            if($containsLetter == 0){
                return false;
            }
            $containsDigit = preg_match('/\d/', $password);
            if($containsDigit == 0){
                return false;
            }
            return true;
        }

        public function isValidPincode($pincode){
            if(strlen($pincode) == 6) return self::isValidNumber($pincode);
            return false;
        }

        public function isEmpty($text){
            if(strcmp($text, '') == 0) {echo 'yes it is empty';return true;}
            else return false;
        }

        public function isPresentWithValue($a, $b, $c, $d, $e){
            if(!isset($a) || !isset($b) || !isset($c) || !isset($d) || !isset($e))
                return false;
            if(self::isEmpty($a) || self::isEmpty($b) || self::isEmpty($c) || self::isEmpty($d) || self::isEmpty($e))
                return false;
            return true;
        }

        public function isValidYear($year){
            $isValidYear = false;
            if(strlen($year) == 4 && self::isValidNumber($year))
                $isValidYear =  true;
            else $isValidYear =  false;
            return $isValidYear;
        }

        public function isValidLoginRequest($request){
            $error = array();
            if(!self::isValidUsername($request->username)){
                $error['errorCode'] = 'INVALID_PARAM';
                $error['errorMessage'] = 'Username must be phoneNumber.';
            }else if(!self::isPasswordSet($request->password)){
                $error['errorCode'] = 'INVALID_PARAM';
                $error['errorMessage'] = 'Password must not be empty.';
            }
            return $error;
        }

        public function isValidRegisterRequest($request){
            $error = array();
            if(!isset($request->firstname) || !self::isValidText($request->firstname)){
                $error['errorCode'] = 'INVALID_PARAM';
                $error['errorMessage'] = 'FistName should not be empty.';  
            }else if(!isset($request->lastname) || !self::isValidText($request->lastname)){
                $error['errorCode'] = 'INVALID_PARAM';
                $error['errorMessage'] = 'LastName should not be empty'; 
            }else if(!isset($request->mailId) || !self::isValidEmail($request->mailId)){
                $error['errorCode'] = 'INVALID_PARAM';
                $error['errorMessage'] = 'Invalid email id.'; 
            }else if(!isset($request->phoneNumber) || !self::isValidPhoneNumber($request->phoneNumber)){
                $error['errorCode'] = 'INVALID_PARAM';
                $error['errorMessage'] = 'Invalid phoneNumber. phoneNumber should be 10 digits. No need for 0 or +91'; 
            }else if(!isset($request->password) || !self::isPassword($request->password)){
                $error['errorCode'] = 'INVALID_PARAM';
                $error['errorMessage'] = 'Password must be atleast 8 chars.\n Password must have atleast 1 digit.'; 
            }
            return $error;

        }
    }
?>