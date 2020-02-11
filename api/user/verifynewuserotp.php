<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
    header('Content-Type: application/json');
    header('Accept: application/json');

    include_once '../../config/Database.php';
    include_once '../../models/User.php';
    include_once '../../models/MyTime.php';

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $database = new Database();
        $db = $database->connect();

        $user = new User($db);
        $response = array();
        $error = "";

        $headers = apache_request_headers();
        try{
            $data = json_decode(file_get_contents("php://input"));
            if(isset($data->otp)){
                $otp = $data->otp;
            }else{
                $error = $error ."Please enter verification OTP";
            }

            if(isset($data->phoneNumber)){
                $phoneNumber = $data->phoneNumber;
            }else{
                $error = $error . "Please enter phone number";
            }

            if(isset($data->encOtp)){
                $otp_header = $data->encOtp;
            }else{
                $error = $error ."Please send ENC OTP value.";
            }

            if(isset($data->otp_sent_time)){
                $otp_sent_time = $data->otp_sent_time;
            }else{
                $error = $error ."Please send OTP_SENT_TIME value.";
            }
            
            if(!empty($error)){
                $response['statusCode'] = "-1";
                $response['statusMessage'] = "The Verify OTP API Failed.";
                $response['errorCode'] = "OTP_MISSING";
                $response['errorMessage'] = $error;
                $response['isVerified'] = "N";
            }else{
                $curr_time = MyTime::currentTime();
                $isExpired = MyTime::getDifference($curr_time, $otp_sent_time);
                $status = $user->validate_otp($otp, $otp_header, $isExpired);
                if($status == -5){
                    $response['statusCode'] = "-2";
                    $response['statusMessage'] = "The Verify OTP API Failed.";
                    $response['errorCode'] = "OTP_EXPIRED";
                    $response['errorMessage'] = "The OTP has Expired";
                    $response['isVerified'] = "N";
                }else{
                    if($status == 1){
                        $usr = array();
                        $user->updateOTPStatus($phoneNumber);
                        $usr = $user->getUserDetails($phoneNumber);
                        $response['user'] = $usr;
                        $response['statusCode'] = "0";
                        $response['statusMessage'] = "The Verify OTP API Passed.";
                        $response['errorCode'] = null;
                        $response['errorMessage'] = null;
                        $response['isVerified'] = "Y";
                    }else{
                        $response['statusCode'] = "-1";
                        $response['statusMessage'] = "The Verify OTP API Failed.";
                        $response['errorCode'] = "OTP_WRONG";
                        $response['errorMessage'] = "The OTP is wrong.";
                        $response['isVerified'] = "N";
                    }
                }
            }
        }catch(Exception $e){
            echo $e;
        }finally{
            $db->close();
        }
        echo json_encode($response);
    }
?>