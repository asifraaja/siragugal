<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
    header('Content-Type: application/json');
    header('Accept: application/json');

    include_once '../../config/Database.php';
    include_once '../../models/User.php';
    include_once '../../models/MyTime.php';
    /**
     * 1. If phoneNumber given
     *      1a. Validate phoneNumber does not already exists
     *      1b. Send an OTP to phoneNumber
     */

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $database = new Database();
        $db = $database->connect();

        $user = new User($db);
        $response = array();
       
        $error = "";
        try{
            //1. If phoneNumber given
            $data = json_decode(file_get_contents("php://input"));
            if(isset($data->phoneNumber)){
                $phoneNumber = $data->phoneNumber;
            }else{
                $error = "Phone Number not specified.";
            }

            if(!empty($error)){
                $response['statusCode'] = "-1";
                $response['statusMessage'] = "The Registration API did not execute successfully";
                $response['errorCode'] = "PARAM_MISSING";
                $response['errorMessage'] = $error;
            }else{
                // 1a. Validate phoneNumber does not already exists
                $user_exists = $user->findOneUser($phoneNumber);
                if($user_exists == 1){
                    $response['statusCode'] = "-1";
                    $response['statusMessage'] = "The Registration API did not execute successfully";
                    $response['errorCode'] = "USER_EXISTS";
                    $response['errorMessage'] = "The ".$phoneNumber."  is already registered.";
                }else{
                    // 1b. Send OTP to phoneNumber
                    $otp_sent = $user->sendOTP($phoneNumber);
                    if($otp_sent != null){
                        $response['user']['otp'] = $user->encrypt_otp($otp_sent);
                        $response['user']['otp_sent_time'] = MyTime::generateOTPEndTime();
                        $response['user']['otpIs'] = $otp_sent;
                    }else{
                        $response['statusCode'] = "-1";
                        $response['statusMessage'] = "The Registration API did not successfully";
                        $response['errorCode'] = "OTP_ERROR";
                        $response['errorMessage'] = "Error while sending OTP";
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