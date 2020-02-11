<?php
    class X{

        public function loginUser($username, $password){
            $res = array();
            
            $query = "SELECT * FROM login_details WHERE username = ? ";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('d', $username);
            $stmt->execute();
            $result = $stmt->get_result();

            $status = "";
            if($result->num_rows == 0){
                $no_such_user = true;   // user does not exists
                $status = "NO_USER";
            }else{
                // compare the otp_flag
                $row = $result->fetch_assoc();
                $otp_flag = $row['otp_fl'];
                $is_locked = $row['is_locked'];
                if($otp_flag == 'N'){
                    $status = "OTP_NEEDED";
                }else if($is_locked == 'Y'){
                    $status = "LOCKED";
                }else{
                    // Validate the password
                    $password_status = self::validate_password($password, $row['password']);
                    if($password_status)
                        $status = "PASSWORD_FAILED";
                    else
                        $status = "LOGIN_SUCCESS";
                }
            }

            switch($status){
                case "NO_USER":
                    $res = self::lockUser($username);     // Check if we can lock the user
                break;

                case "LOCKED":
                    $res = self::lockUser($username);     // Check if we can lock the user
                break;

                case "PASSWORD_FAILED":
                    $res = self::lockUser($username);     // Check if we can lock the user
                break;

                case "OTP_NEEDED":
                    $res['statusCode'] = "OTP_NEEDED";
                    $response['statusMessage'] = "The OTP must be entered to login";
                    $otp_sent = $user->sendOTP($username);
                    
                    if($otp_sent != null){
                        $response['user']['otp'] = $user->encrypt_otp($otp_sent);
                        $response['user']['otp_sent_time'] = MyTime::generateOTPEndTime();
                        $response['user']['otpIs'] = $otp_sent;
                        $response['user']['phoneNumber'] = username;
                    }else{
                        $response['statusCode'] = "-1";
                        $response['statusMessage'] = "The Registration API did not successfully";
                        $response['errorCode'] = "OTP_ERROR";
                        $response['errorMessage'] = "Error while sending OTP";
                    }
                break;

                case "LOGIN_SUCCESS":
                    // Login successfull
                    self::updateLoginDetails($username);     // Check if can unlock the user and Update the last_logged_in_date_time
                    // Getting last login details
                    $userId = $row['usr_id'];
                    $lastLoggedIn = $row['last_login_dttm'];
                    $isAdmin = $row['admin_fl'];
                    $isVolunteer = $row['vol_fl'];
                    $phoneNumber = $row['username'];
                    // Getting user details
                    $user_details_query = "SELECT * FROM user_details WHERE usr_id = ?";
                    $stmt = $this->conn->prepare($user_details_query);
                    $stmt->bind_param('d', $userId);
                    $stmt->execute();
                    $user_details_result = $stmt->get_result();
                    $user_details_row = $user_details_result->fetch_assoc();

                    $res['errorCode'] = "0";
                    $res['errorMessage'] = null;
                    $user = array();
                    $user['userId'] = $userId;
                    $user['firstname'] = $user_details_row['firstname'];
                    $user['lastname'] = $user_details_row['lastname'];
                    $user['mail_id'] = $user_details_row['mail_id'];
                    $user['dob'] = $user_details_row['dob'];
                    $user['phoneNumber'] = $phoneNumber;
                    $user['lastLoggedInTime'] = $lastLoggedIn;
                    $user['isAdmin'] = $isAdmin; 
                    $user['isVolunteer'] = $isVolunteer; 
                    $res['user'] = $user;
                break;
            }
        }







        public function loginUser2($username, $password){
            /**
             * 1. Validate if such a user exists 
             *  1.1 if user exists 
             *      1.1.1 Validate it is not locked, if locked throw message saying user locked, return
             *      1.1.2 Validate if otp_fl = 'Y', if not -> throw a message saying saying otp_needed, return
             *      1.1.3 Validate the password, if not valid -> throw message saying wrong credentails, return
             *      1.1.4 Update the login details
             * 2. throw error saying user does not exists
             */
            $res = array();

            $query = "SELECT * FROM login_details WHERE username = ? AND otp_fl='Y' AND is_locked = 'N'";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('d', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows == 0){
                $no_such_user = true;
            }else{
                // comparing passwords
                $row = $result->fetch_assoc();
                $status = self::validate_password($password, $row['password']);
                if($status)
                    $no_such_user = false;
                else   
                    $no_such_user = true;
            }
            if($no_such_user == true){
                // Login has failed
                $res = self::lockUser($username);     // Check if we can lock the user
            }else{
                // Login successfull
                self::updateLoginDetails($username);     // Check if can unlock the user and Update the last_logged_in_date_time
                // Getting last login details
                $userId = $row['usr_id'];
                $lastLoggedIn = $row['last_login_dttm'];
                $isAdmin = $row['admin_fl'];
                $isVolunteer = $row['vol_fl'];
                $phoneNumber = $row['username'];
                // Getting user details
                $user_details_query = "SELECT * FROM user_details WHERE usr_id = ?";
                $stmt = $this->conn->prepare($user_details_query);
                $stmt->bind_param('d', $userId);
                $stmt->execute();
                $user_details_result = $stmt->get_result();
                $user_details_row = $user_details_result->fetch_assoc();

                $res['errorCode'] = "0";
                $res['errorMessage'] = null;
                $user = array();
                $user['userId'] = $userId;
                $user['firstname'] = $user_details_row['firstname'];
                $user['lastname'] = $user_details_row['lastname'];
                $user['mail_id'] = $user_details_row['mail_id'];
                $user['dob'] = $user_details_row['dob'];
                $user['phoneNumber'] = $phoneNumber;
                $user['lastLoggedInTime'] = $lastLoggedIn;
                $user['isAdmin'] = $isAdmin; 
                $user['isVolunteer'] = $isVolunteer; 
                $res['user'] = $user;
            }
            return $res;
        }
    }
?>