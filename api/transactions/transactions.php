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
     * Return first 10 transactions
     */
       
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $database = new Database();
        $db = $database->connect();
        $user = new User($db);
        $validator = new Validator();
        $response = array();
        $error = array();

        try{
            $JSON = file_get_contents("php://input");
            $request = new RegisterRequest($JSON);
            $error = $validator->isValidRegisterRequest($request);

            if(empty(error)){
                $res = $user->createNewUser($request);
                $error = $res->error;
                if(empty($error)){
                    $otpRes = $user->sendOTP($phoneNumber);
                    if($otp_sent != null){            
                        $response['user']['otp'] = $user->encrypt_otp($otp_sent);
                        $response['user']['otp_sent_time'] = MyTime::generateOTPEndTime();
                        $response['user']['otpIs'] = $otp_sent;
                    }else{
                        $error['errorCode'] = 'OTP_ERROR';
                        $error['errorMessage'] = 'Error while sending OTP.';    
                    }
                }
            }
        }catch(){
            echo "Exception during registration: \n".$e; 
        }finally{
            $db->close();
        }

        if(!empty($error)){
            $response['error'] = $error;
            response['status'] = '-1';
            response['statusMessage'] = 'Registration request failed';
        }else{
            response['status'] = '1';
            response['statusMessage'] = 'User is registered successfully.'; 
        }
        
        echo json_encode($response);

    }
?>