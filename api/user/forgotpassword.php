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

    /**
     * When the user forgets the password. 
     * 1. User clicks the ForgotPassword.
     * 2. PhoneNumber should be passed.
     * 3. If the phoneNumber is valid then send an OTP
     * 4. else throw error
     */
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $database = new Database();
        $db = $database->connect();

        $user = new User($db);

        $response = array();
        $validator = new Validator();

        $error = "";
        try{
            $data = json_decode(file_get_contents("php://input"));
            
            if(isset($data->phoneNumber) && $validator->isValidNumber($data->phoneNumber) && $validator->isValidPhoneNumber($data->phoneNumber)){
                $phoneNumber = $data->phoneNumber;
            }else
                $error = $error."Phone Number must be 10 digit number. Do not add 0 in the beginning";
            
            if(!empty($error)){
                $response['statusCode'] = "-1";
                $response['statusMessage'] = "The Forgot Password API did not execute successfully";
                $response['errorCode'] = "PARAM_MISSING";
                $response['errorMessage'] = $error;
            }else{
                $user_exists = $user->findOneUser($phoneNumber);
                if($user_exists == 1){
                    // Valid user
                    $otp_sent = $user->sendForgotOTP($phoneNumber);
                            
                    if(strpos($otp_sent, '-1') === 0){
                        // error whild sending OTP
                        $response['statusCode'] = "-1";
                        $response['statusMessage'] = "The Forgot Password API did not successfully";
                        $response['errorCode'] = $otp_sent;
                        $response['errorMessage'] = "Error while sending OTP";
                    }else{
                        // OTP sent successfully
                        // $user->storeOTP($user->encrypt_otp($otp_sent), MyTime::generateOTPEndTime(), $otp_resent);
                        $response['user']['otp'] = $user->encrypt_otp($otp_sent);
                        $response['user']['otp_sent_time'] = MyTime::generateOTPEndTime();
                        $response['user']['otp_real'] = $otp_sent;
                        $response['user']['forgot_otp'] = "Y";
                        $response['statusCode'] = "0";
                        $response['statusMessage'] = "The Forgot Password API executed successfully";
                        $response['errorCode'] = null;
                        $response['errorMessage'] = null;
                    }
                }else{
                    // No such user
                    $response['statusCode'] = "-1";
                    $response['statusMessage'] = "The Forgot Password API did not execute successfully";
                    $response['errorCode'] = "NO_SUCH_USER";
                    $response['errorMessage'] = "No such user exists";
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