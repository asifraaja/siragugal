<?php
    include_once '../../config/Properties.php';
    include_once '../../config/textlocal.class.php';
    include_once '../../models/MyTime.php';
    include_once '../../config/Database.php';

    class User{

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
         * encrypt the otp
         * @param otp - otp that is sent
         */
        public function encrypt_otp($otp){
            $encrypted_otp = password_hash($otp, PASSWORD_BCRYPT, array('cost'=>$this->props['OTPCOST']));
            return $encrypted_otp;
        }

        /**
         * validate the otp
         * @param otp - otp sent to user
         * @param encrypted_otp - encrypted otp from the headers
         * @param isExpired - otp expired (true/false)
         */
        public function validate_otp($otp, $encrypted_otp, $isExpired){
            // echo "Is expired : ".$isExpired;
            if($isExpired == "YES"){
                return -5;
            }
            $status = password_verify($otp, $encrypted_otp);
            // echo "status : ".$status;
            return intval($status);
        }

        /**
         * updates the num_of_tries, is_locked & last_loggin_dttm
         * @param username - phonenumber of the user
         */
        public function updateLoginDetails($username){
            $query = "UPDATE login_details SET num_of_tries = 0, is_locked = 'N', last_login_dttm = CURRENT_TIMESTAMP WHERE username = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('d', $username);
            $result = $stmt->execute();
        }

        /**
         * finds where the phonenumber exists in db or not
         * @param phoneNumber
         * @return (0/1)
         */
        public function findOneUser($phoneNumber){
            $query = "SELECT * FROM login_details WHERE username = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('d', $phoneNumber);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows == 0){
                return 0;
            } else{
                return 1;
            }
        }

        /**
         * locks the user
         * @param username - phone number to lock
         */
        public function lockUser($username){
            if(self::findOneUser($username) == 0){
                $res['errorCode'] = "NO_SUCH_USER";
                $res['errorMessage'] = "Username or Password Wrong";
            }else{
                // Getting the number of tries user failed to login
                $query = "SELECT num_of_tries,is_locked FROM login_details WHERE username = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param('d', $username);
                $stmt->execute();
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $num_of_tries = intval($row['num_of_tries']);
                $is_locked = $row['is_locked'];

                if($num_of_tries < 4 && $is_locked == 'N'){
                    // Increment the count and Warn the user
                    $num_of_tries = $num_of_tries + 1;
                    $update_try_count_query = "UPDATE login_details SET num_of_tries = ? WHERE username = ?";
                    $stmt = $this->conn->prepare($update_try_count_query);
                    $stmt->bind_param('dd', $num_of_tries, $username);
                    $update_try_count_result = $stmt->execute();
                    if($update_try_count_result == false)
                        return;
                    $remainingTimes = 5 - $num_of_tries;
                    $res['errorCode'] = "WARN_USER";
                    $res['errorMessage'] = "Wrong Credentials. Repeat it " .$remainingTimes. " . Your Account will get locked.";
                }else{
                    // Lock the account
                    $num_of_tries = 5;
                    $update_try_count_query = "UPDATE login_details SET num_of_tries = ?, is_locked = 'Y' WHERE username = ?";
                    $stmt = $this->conn->prepare($update_try_count_query);
                    $stmt->bind_param('dd', $num_of_tries, $username);
                    $update_try_count_result = $stmt->execute();
                    if($update_try_count_result == false)
                        return;
                    $remainingTimes = 5 - $num_of_tries;
                    $res['errorCode'] = "LOCKED";
                    $res['errorMessage'] = "Sorry Account Locked. Please try Forgot Password.";
                }
            }
            return $res;
        }

        /**
         * encrypt the password while storing the password in db
         * @param password
         * @return encrypted_password
         */
        private function encrypt_password($password){
            $encrypted_password = password_hash($password, PASSWORD_BCRYPT, array('cost'=>$this->props['PASSWORDCOST']));
            return $encrypted_password;
        }

        /**
         * validate the password matches with the encrypted_password from db
         * @param password
         * @param encrypted_password
         */
        private function validate_password($password, $encrypted_password){
            $status = password_verify($password, $encrypted_password);
            return $status;
        }

        /**
         * create a user
         * @param firstname - firstname of the user
         * @param lastname - lastname of the user
         * @param mail_id - email Id of the user
         * @param phoneNumber - phone number of the user to login
         * @param dob - date of birth 
         * @param password - password the user wants to set
         */
        public function createUser($firstname, $lastname, $mail_id, $phoneNumber, $dob, $password){
            $res = array();

            // Insert the user details into the database
            $query = "INSERT INTO user_details(firstname,lastname,ph_no,mail_id,dob) VALUES(?,?,?,?,?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('ssdss',$firstname, $lastname, $phoneNumber, $mail_id, $dob);
            $stmt->execute();

            // Validate if the user is created
            $query = "SELECT usr_id FROM user_details WHERE ph_no = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('d', $phoneNumber);
            $result = $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows == 0){
                $res['errorCode'] = '1';
                $res['errorMessage'] = "Error while creating user.";
            }else{
                $row = $result->fetch_assoc();
                $userId = $row['usr_id'];

                // insert the password in login_details
                $encrypted_password = self::encrypt_password($password);
                $query = "INSERT INTO login_details(usr_id,username,password,last_login_dttm) VALUES(?,?,?,?)";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param('ddss', $userId, $phoneNumber, $encrypted_password, $timestamp);
                $result = $stmt->execute();

                $res['errorCode'] = '0';
                $res['errorMessage'] = "User created successfully";
                $user = array();
                $user['userId'] = $userId;
                $user['firstname'] = $firstname;
                $user['lastname'] = $lastname;
                $user['mail_id'] = $mail_id;
                $user['dob'] = $dob;
                $user['phoneNumber'] = $phoneNumber;
                $user['lastLoggedInTime'] = $timestamp;
                $user['isAdmin'] = 'N';
                $user['isVolunteer'] = 'N';
                $res['user'] = $user;
            }
            return $res;
        }

        /**
         * logs into the user account
         * @param username - phone number to login
         * @param password - password for the user
         */
        public function loginUser($username, $password){
            $res = array();
            
            // check if the such user exists
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
                // Check the OTP Flag
                $row = $result->fetch_assoc();
                $otp_flag = $row['otp_fl'];
                $is_locked = $row['is_locked'];
                if($otp_flag != 'Y'){
                    $status = "OTP_NEEDED";
                }else if($is_locked == 'Y'){
                    $status = "LOCKED";
                }else{
                    // Validate the password
                    $password_status = self::validate_password($password, $row['password']);
                    if($password_status) $status = "LOGIN_SUCCESS";
                    else $status = "PASSWORD_FAILED";
                }
            }

            // Respond based on the status
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
                    // send an OTP to the phoneNumber
                    $res['errorCode'] = "OTP_NEEDED";
                    $otp_sent = self::sendOTP($username);
                    
                    if($otp_sent != null){
                        // self::storeOTP($user->encrypt_otp($otp_sent), MyTime::generateOTPEndTime(), $otp_sent);
                        $res['user']['otp'] = self::encrypt_otp($otp_sent);
                        $res['user']['otp_sent_time'] = MyTime::generateOTPEndTime();
                        $res['user']['otpIs'] = $otp_sent;
                        $res['user']['phoneNumber'] = $username;
                    }else{
                        $res['statusCode'] = "-1";
                        $res['statusMessage'] = "The Registration API did not successfully";
                        $res['errorCode'] = "OTP_ERROR";
                        $res['errorMessage'] = "Error while sending OTP";
                    }
                break;

                case "LOGIN_SUCCESS":
                    // Login successfull
                    self::updateLoginDetails($username);     // Check if we can unlock the user and Update the last_logged_in_date_time
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
                    $user['maskedContact'] = self::maskContact($phoneNumber);
                    $res['user'] = $user;
                break;
            }
            return $res;
        }

        /**
         * send an otp to the phonenumber
         * @param phoneNumber
         */
        public function sendOTP2($phoneNumber){
            // $textlocal = new Textlocal($this->props['TEXTLOCALEMAIL'], $this->props['TEXTLOCALHASH']);
            $textlocal = new Textlocal(false, false, $this->props['TEXTLOCALAPIKEY']);
            $phoneNumber = '91'.$phoneNumber;
            $numbers = array($phoneNumber);
            $sender = $this->props['TEXTLOCALSENDER'];
            $otp_number = mt_rand($this->props['OTPMINIMUM'], $this->props['OTPMAXIMUM']); 
            $message = "The OTP for Siragugal trust is ".$otp_number;
            // echo $message;
            try{
                $result = $textlocal->sendSms($numbers, $message, $sender);
                // echo "Result is : ".$result;
                return $otp_number;
            }catch(Exception $e){
                // echo "Exception :".$e;
                return null;
            }
        }

        /**
         * sends an OTP to the phoneNumber
         * @param phoneNumber
         */
        public function sendOTP($phoneNumber){
            // setting the details from TEXTLOCAL
            $username = "reebas7@gmail.com";    // TEXTLCL account
            $hash = "2a21f3d9da52f0b467276793f3d949e024118339d77f700fbc58f94ed641d798";         // hashkey from TEXTLOCAL
            $test = "0";    // sends the message. 1->doesn't sends. 0->sends to Phone.
            $sender = "SIRAGU";     //$this->props['TEXTLOCALSENDER'];      // This is who the message appears to be from.
            
            $numbers = "91".$phoneNumber;       // A single number or a comma-seperated list of numbers
            $message = $this->props['TEXTLOCALTEMPLATE'];
            $otp_number = mt_rand($this->props['OTPMINIMUM'], $this->props['OTPMAXIMUM']); 
            $message = str_replace("?", $otp_number, $message);
            // echo $sender.$numbers.$message;
            
            $message = urlencode($message);     // encode the message
            $data = "username=".$username."&hash=".$hash."&message=".$message."&sender=".$sender."&numbers=".$numbers."&test=".$test;
            
            /**
             * 1. Create a request
             * 2. Hit the request
             * 3. Get the response
             * 4. Decode the json
             * 5. Return the status
             */
            $ch = curl_init('http://api.textlocal.in/send/?');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch); // This is the result from the API
            $output = json_decode($result);
            // echo $result;
            $status = $output->status;
            if($status == "success"){

            }else{
                $otp_number = null;
            }
            curl_close($ch);
            return $otp_number;
        }

        /**
         * changes the password
         * @param password - new password
         * @param phoneNumber - phoneNumber to change password for
         */
        public function changePassword($password, $phoneNumber){
            $res = array();
            $encrypted_password = self::encrypt_password($password);
            $query = "UPDATE login_details SET password = ? where username = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('sd', $encrypted_password, $phoneNumber);
            $result = $stmt->execute();
            self::updateLockedStatus($phoneNumber);
            return $result;
        }

        /**
         * Resets the locked status to N
         */
        public function updateLockedStatus($phoneNumber){
            $isLocked = 'N'; $numOfTries = 0;
            $query = "UPDATE login_details SET is_locked = ?,num_of_tries = ? where username = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('sdd', $isLocked, $numOfTries, $phoneNumber);
            $result = $stmt->execute();
        }

        /**
         * update the OTP status for a user
         * @param phoneNumber 
         */
        public function updateOTPStatus($phoneNumber){
            $query = "UPDATE login_details SET otp_fl = 'Y' where username = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('d', $phoneNumber);
            $result = $stmt->execute();
            return $result;
        }

        /**
         * edits the user details 
         * @param firstname
         * @param lastname
         * @param mail_id
         * @param dob
         */
        public function editUser($firstname, $lastname, $mail_id, $dob){
            $query = "UPDATE user_details SET firstname=?, lastname=?, mail_id=?, dob=?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('ssss', $firstname, $lastname, $mail_id, $dob);
            $result = $stmt->execute();
            return $result;
        }

        /**
         * returns the details about the user (id, fname, lname, phNo, mail, dob, uname, loggedin, isAdmin, isVol)
         * @param phoneNumber
         */
        public function getUserDetails($phoneNumber){
            $user = array();
            $newDatabase = new Database();
            $newConn = $newDatabase->connect();
            // $get_user_id_query = "SELECT * FROM user_details WHERE ph_no = '".$phoneNumber."'";
            $QUERY = "SELECT * FROM user_details WHERE ph_no = '".$phoneNumber."'";
            $result = $newConn->query($QUERY);
            if($result->num_rows != 0){
                $userRow = $result->fetch_assoc();
                $userId = $userRow['usr_id'];
                
                $query = "SELECT ud.usr_id AS usr_id, firstname, lastname, ph_no, mail_id, dob, username, last_login_dttm, admin_fl, vol_fl FROM user_details ud, login_details ld WHERE ud.usr_id=ld.usr_id AND ud.usr_id=".$userId;
                $userResult = $newConn->query($query);
                $row = $userResult->fetch_assoc();
                
                $user['userId'] = $row['usr_id'];
                $user['firstname'] = $row['firstname'];
                $user['lastname'] = $row['lastname'];
                $user['phoneNumber'] = $row['ph_no'];
                $user['mail_id'] = $row['mail_id'];
                $user['dob'] = $row['dob'];
                $user['username'] = $row['username'];
                $user['lastLoggedInTime'] = $row['last_login_dttm'];
                $user['isAdmin'] = $row['admin_fl'];
                $user['isVolunteer'] = $row['vol_fl'];
                $user['maskedContact'] = self::maskContact($phoneNumber);
            }
            return $user;
        }

        /**
         * sends the OTP when user has forgot the password
         * @param phoneNumber
         */
        public function sendForgotOTP($phoneNumber){
            $username = "reebas7@gmail.com";
            $hash = "2a21f3d9da52f0b467276793f3d949e024118339d77f700fbc58f94ed641d798";

            $test = "0";
            $sender = "SIRAGU";//$this->props['TEXTLOCALSENDER']; // This is who the message appears to be from.
            $numbers = "91".$phoneNumber; // A single number or a comma-seperated list of numbers
            $message = $this->props['TEXTLOCALTEMPLATE'];
            $otp_number = mt_rand($this->props['OTPMINIMUM'], $this->props['OTPMAXIMUM']); 
            $message = str_replace("?", $otp_number, $message);
            // echo $sender.$numbers.$message;
            
            $message = urlencode($message);
            $data = "username=".$username."&hash=".$hash."&message=".$message."&sender=".$sender."&numbers=".$numbers."&test=".$test;
            $ch = curl_init('http://api.textlocal.in/send/?');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch); // This is the result from the API
            $output = json_decode($result);
            $status = $output->status;
            if($status == "success"){

            }else{
                $errors = $output->errors;
                $error = $errors[0];
                $otp_number = "-1".$error->message;
            }
            curl_close($ch);
            return $otp_number;
        }

        /**
         * Masks the phoneNumber
         * @param - phoneNumber
         * @return - maskedPhoneNumber(12*****345)
         */
        public function maskContact($phoneNumber){
            $len = strlen($phoneNumber);
            $lead = substr($phoneNumber, 0, 2);
            $trail = substr($phoneNumber, $len - 3, 3);
            $maskContact = $lead.'*****'.$trail;
            return $maskContact;
        }

        /**
         * Store the OTP details in DB.
         */
        public function storeOTP($phoneNumber, $encOtp, $endTime){
            // Delete the existing OTP if any 
            $query = "DELETE FROM otp WHERE phoneNumber = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('s', $phoneNumber);
            $delete_otp_result = $stmt->execute();
            if($delete_contact_result == false) return -1;
            // Insert the OTP value
            $query = "INSERT INTO otp(phoneNumber, encOtp, endTime) VALUES (?,?,?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('dss', $phoneNumber, $encOtp, $endTime);
            $insert_otp_result = $stmt->execute();
            if($insert_otp_result == false) return -1;
            return 1;
        }

        /**
         * Verify the OTP from DB
         */
        public function verifyOTP($phoneNumber, $encOtp){
            $curr_time = MyTime::currentTime();
            $query = "SELECT * FROM otp WHERE phoneNumber = ? and encOtp = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('ss', $phonNumber, $encOtp);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows == 0) {
                $res['error'] = '-1';
                $res['errorCode'] = 'WRONG OTP';
                $res['errorMessage'] = 'You have entered the Wrong OTP';
                return $res;
            }
            $row = $result->fetch_assoc();
            $otp_sent_time = $row['endTime'];
            $isExpired = MyTime::getDifference($curr_time, $otp_sent_time);
            if($isExpired == 'YES'){
                $res['error'] = '-1';
                $res['errorCode'] = 'OTP EXPIRED';
                $res['errorMessage'] = 'The OTP has Expired';
                return $res;
            }
            $res['error'] = '0';
            $res['errorCode'] = '';
            $res['errorMessage'] = '';            
            return $res;
        }

        /**
         * Returns (userId, userEmail, phoneNumber)
         */
        public function getAllUsers(){
            $query = "SELECT ud.usr_id AS usr_id, ph_no, mail_id FROM user_details ud, login_details ld WHERE ud.usr_id=ld.usr_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();

            $users = array();
            while($row = $result->fetch_assoc()){
                $user = array();
                $user['userId'] = $row['usr_id'];
                $user['phoneNumber'] = $row['ph_no'];
                $user['emailId'] = $row['mail_id'];
                array_push($users, $user);
            }
            return $users;
        }

        public function updateOTPStatusAndGetUserDetails($phoneNumber){
            self::updateOTPStatus($phoneNumber);
            return self::getUserDetails($phoneNumber);
        }

        public function updateVolunteerStatus($userId, $volunteerStatus){
            $query = "UPDATE login_details SET vol_fl = ? where usr_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('sd', $volunteerStatus, $userId);
            $result = $stmt->execute();
            return $result;
        }
    }
?>