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
     * Resends the OTP.
     */
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $database = new Database();
        $db = $database->connect();

        $user = new User($db);
        $response = array();
       
        $error = "";
        try{
            $data = json_decode(file_get_contents("php://input"));
            if(isset($data->phoneNumber)){
                $phoneNumber = $data->phoneNumber;
            }else{
                $error = "Phone Number not specified.";
            }

            $otp_resent = $user->sendForgotOTP($phoneNumber);
            if(strpos($otp_resent, '-1') === 0){
                $response['errorCode']="OTP_ERROR";
                $response['errorMessage']="Unable to send OTP";
                $response['statusCode']="-1";
                $response['statusMessage']="The OTP API has failed.";
            }else{
                $usr = array();
                // $user->storeOTP($user->encrypt_otp($otp_sent), MyTime::generateOTPEndTime(), $otp_resent);
                $usr['otp'] = $user->encrypt_otp($otp_resent);
                $usr['otp_sent_time'] = MyTime::generateOTPEndTime();
                $usr['otp_real'] = $otp_resent;
                $response['user'] = $usr;
                $response['errorCode']=null;
                $response['errorMessage']=null;
                $response['statusCode']="0";
                $response['statusMessage']="The OTP has been resent";
            }
        }catch(Exception $e){
            echo "Exception : \n".$e;
        }finally{
            $db->close();
        }
        echo json_encode($response);
    }
?>