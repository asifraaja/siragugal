<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
    header('Content-Type: application/json');
    header('Accept: application/json');

    include_once '../../config/Database.php';
    include_once '../../config/SingletonDB.php';
    include_once '../../models/User.php';
    include_once '../../config/Validator.php';
    include_once '../../models/MyTime.php';
    include '../../dto/request/RegisterRequest.php';

    error_reporting(E_ALL); 
    ini_set("display_errors", 1); 
    /**
     * Creates a new user.
     * 1. When user enters (Firstname, Lastname, Email, PhoneNumber, Password).
     * 2. If all the fields are valid & no such phoneNumber already exists then -> createNewUser
     * 3. Else throw appropriate error
     */
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        // $database = new Database();
        $database = SingletonDB::getInstance();
        $db = $database->connect();
        $user = new User($db);
        $validator = new Validator();
        $response = array();
        $error = array();

        try{
            $JSON = file_get_contents("php://input");
            $request = new RegisterRequest($JSON);
            $error = $validator->isValidRegisterRequest($request);

            if(empty($error)){
                $res = $user->registerNewUser($request);
                $error = $res['error'];
                if(empty($error)){
                    $otp_sent = $user->sendOTP($request->phoneNumber);
                    if($otp_sent != null){            
                        $res['user']['otp'] = $user->encrypt_otp($otp_sent);
                        $res['user']['otp_sent_time'] = MyTime::generateOTPEndTime();
                        $res['user']['otpIs'] = $otp_sent;
                    }else{
                        $error['errorCode'] = 'OTP_ERROR';
                        $error['errorMessage'] = 'Error while sending OTP.';    
                    }
                }
                $response = $res;
            }
        }catch(Exception $e){
            echo "Exception during registration: \n".$e; 
        }finally{
            $db->close();
        }

        if(!empty($error)){
            $response['error'] = $error;
            $response['status'] = '-1';
            $response['statusMessage'] = 'Registration request failed';
        }else{
            $response['status'] = '1';
            $response['statusMessage'] = 'User is registered successfully.'; 
        }
        
        echo json_encode($response);

    }
?>