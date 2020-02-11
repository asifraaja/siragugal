<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
    header('Content-Type: application/json');
    header('Accept: application/json');

    include_once '../../config/Database.php';
    include_once '../../models/User.php';
    include_once '../../config/Validator.php';
    include_once '../../models/MyTime.php';

    error_reporting(E_ALL); 
    ini_set("display_errors", 1); 
    /**
     * Creates a new user.
     * 1. When user enters (Firstname, Lastname, Email, PhoneNumber, Password).
     * 2. If all the fields are valid & no such phoneNumber already exists then -> createNewUser
     * 3. Else throw appropriate error
     */
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $database = new Database();
        $db = $database->connect();

        $user = new User($db);

        $validator = new Validator();

        $response = array();

        $error = "";
        try{
            /** Getting the json body from the request */
            $DATA = file_get_contents("php://input");
            $data = json_decode($DATA);
            
            if(isset($data->firstname) && $validator->isValidText($data->firstname)){
                $firstname = $data->firstname;
            }else{
                $error = $error."FirstName must be alphabetical";
            }
        
            if(!isset($data->lastname)){
                $lastname = "";
            }else{
                if($validator->isValidText($data->lastname))
                    $lastname = $data->lastname;
                else
                    $error = $error."Lastname must be alphabetical";
            }

            if(isset($data->mail_id) && $validator->isValidEmail(strtolower($data->mail_id))){
            $mail_id = strtolower($data->mail_id);
            }else
                $error = $error."Mail Id format is invalid";

            if(isset($data->phoneNumber) && $validator->isValidNumber($data->phoneNumber) && $validator->isValidPhoneNumber($data->phoneNumber)){
                $phoneNumber = $data->phoneNumber;
            }else
                $error = $error."Phone Number must be a 10 digit numerical and should not start with 0";

            if(isset($data->dob)){
                $dob = $data->dob;
            }else{
                $dob = "2018-06-21";
            }
            if(isset($data->password)){
                $password = $data->password;
            }else
                $error = $error."Password is compulsory";

            if(!empty($error)){
                $response['statusCode'] = "-1";
                $response['statusMessage'] = "The Registration API did not execute successfully";
                $response['errorCode'] = "PARAM_MISSING";
                $response['errorMessage'] = $error;
            }else{
                /**
                 * check if user exists
                 */
                $user_exists = $user->findOneUser($phoneNumber);
                if($user_exists == 1){
                    $response['statusCode'] = "-1";
                    $response['statusMessage'] = "The Registration API did not execute successfully";
                    $response['errorCode'] = "USER_EXISTS";
                    $response['errorMessage'] = "The ".$phoneNumber."  is already registered.";
                }else{
                    /**
                     * if user does not exists, create a new user
                     */
                    $res = $user->createUser($firstname, $lastname, $mail_id, $phoneNumber, $dob, $password);
                    if($res['errorCode'] == "0"){
                        $response = $res;
                        $response['statusCode'] = "0";
                        $response['statusMessage'] = "The Registration API is executed successfully";
                        $otp_sent = $user->sendOTP($phoneNumber);
                        
                        if($otp_sent != null){
                            // storeOTP($user->encrypt_otp($otp_sent), MyTime::generateOTPEndTime(), $otp_sent);
                            $response['user']['otp'] = $user->encrypt_otp($otp_sent);
                            $response['user']['otp_sent_time'] = MyTime::generateOTPEndTime();
                            $response['user']['otpIs'] = $otp_sent;
                        }else{
                            $response['statusCode'] = "-1";
                            $response['statusMessage'] = "The Registration API did not successfully";
                            $response['errorCode'] = "OTP_ERROR";
                            $response['errorMessage'] = "Error while sending OTP";
                        }
                    }else{
                        $response = $res;
                        $resposne['statusCode'] = "-1";
                        $response['statusMessage'] = "The Registration API is not executed successfully";
                    }
                }
            }
        }catch(Exception $e){
            echo "Exception : \n".$e;
        }finally{
            $db->close();
        }
        
        echo json_encode($response);
    }
?>