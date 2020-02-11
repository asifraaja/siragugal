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
     * 1. Verify the OTP
     * 2. If userDetails present
     *      2a. Create a new User
     * 
     */

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $database = new Database();
        $db = $database->connect();

        $user = new User($db);
        $response = array();
        $error = "";

        $headers = apache_request_headers();
        try{
            // 1. Verify OTP 
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

            if(isset($data ->forgot_otp)){
                $response['user']['forgot_otp'] = $data->forgot_otp;
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
                    $response['statusCode'] = "-1";
                    $response['statusMessage'] = "The Verify OTP API Failed.";
                    $response['errorCode'] = "OTP_EXPIRED";
                    $response['errorMessage'] = "The OTP has Expired";
                    $response['isVerified'] = "N";
                }else{
                    if($status == 1){
                        // 2. If user details present
                        $firstname = $data->firstname;

                        if(!isset($data->lastname)) $lastname = "";
                        else $lastname = $data->lastname;

                        $mail_id = strtolower($data->mail_id);

                        $phoneNumber = $data->phoneNumber;

                        if(isset($data->dob)) $dob = $data->dob;
                        else $dob = "2018-06-21";

                        $password = $data->password;

                        //2a. Create a new user
                        $res = $user->createUser($firstname, $lastname, $mail_id, $phoneNumber, $dob, $password);
                        if($res['errorCode'] == "0"){
                            $response = $res;
                            $response['statusCode'] = "0";
                            $response['statusMessage'] = "The Registration API is executed successfully";
                        }else{
                            $response = $res;
                            $resposne['statusCode'] = "-1";
                            $response['statusMessage'] = "The Registration API is not executed successfully";
                        }
                            
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