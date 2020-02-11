<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
    header('Content-Type: application/json');
    header('Accept: application/json');

    include_once '../../config/Database.php';
    include_once '../../models/User.php';
    include_once '../../config/Validator.php';

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $database = new Database();
        $db = $database->connect();

        $user = new User($db);

        $validator = new Validator();

        $response = array();
        $headers = apache_request_headers();
        $error = "";
        try{
            $data = json_decode(file_get_contents("php://input"));
            if(isset($data->password) && $validator->isPassword($data->password)){
                $password = $data->password;
            }else{
                $error = $error."Password must be minimum 8 characters. It should have Atleast 1 alphabet and 1 number.";
            }

            if(isset($data->confirmPassword) && $validator->isPassword($data->confirmPassword)){
                $confirm_password = $data->confirmPassword;
            }else{
                $error = $error."Password must be minimum 8 characters. Should have Atleast 1 alphabet and 1 number.";
            }

            if(isset($data->phoneNumber) && $validator->isValidNumber($data->phoneNumber) && $validator->isValidPhoneNumber($data->phoneNumber)){
                $phoneNumber = $data->phoneNumber;
            }else{
                $error = $error."Phone Number must be 10 digit number";
            }

            if(isset($data->forgot_otp)){
                $otp = $data->forgot_otp;
            }else{
                $otp = 'N';
            }

            if(!empty($error)){
                $response['errorCode'] = "PARAM_MISSING";
                $response['errorMessage'] = $error;
                $response['statusCode'] = "-1";
                $response['statusMessage'] = "The Change Password API failed";
            }else{
                
                if(intval(strcmp($confirm_password, $password)) != intval(0)){
                    $response['errorCode'] = "PARAM_MISSING";
                    $response['errorMessage'] = "Both the passwords are different.";
                    $response['statusCode'] = "-1";
                    $response['statusMessage'] = "The Change Password API failed";
                }else{
                    if($otp == "Y"){
                        if(isset($data->isVerified)){
                            $isVerified = $data->isVerified;
                        }else{
                            $response['errorCode'] = "OTP_NOT_VERIFIED";
                            $response['errorMessage'] = "The OTP has not been yet verified";
                            $response['statusCode'] = "-1";
                            $response['statusMessage'] = "The Change Password API failed";
                        }
        
                        if($isVerified == "N"){
                            $response['errorCode'] = "OTP_NOT_VERIFIED";
                            $response['errorMessage'] = "The OTP has not been yet verified";
                            $response['statusCode'] = "-1";
                            $response['statusMessage'] = "The Change Password API failed";
                        }else if($isVerified == "Y"){
                            if($user->findOneUser($phoneNumber) == 1){
                                $user->changePassword($password, $phoneNumber);
                                $response['errorCode'] = null;
                                $response['errorMessage'] = null;
                                $response['statusCode'] = "0";
                                $response['statusMessage'] = "The Change Password API Success.";
                            }else{
                                $response['errorCode'] = "NO_SUCH_USER";
                                $response['errorMessage'] = "There is no such user";
                                $response['statusCode'] = "-1";
                                $response['statusMessage'] = "The Change Password API failed";
                            }
                        }
                    }else{
                        if($user->findOneUser($phoneNumber) == 1){
                            $user->changePassword($password, $phoneNumber);
                            $response['errorCode'] = null;
                            $response['errorMessage'] = null;
                            $response['statusCode'] = "0";
                            $response['statusMessage'] = "The Change Password API Success.";
                        }else{
                            $response['errorCode'] = "NO_SUCH_USER";
                            $response['errorMessage'] = "There is no such user";
                            $response['statusCode'] = "-1";
                            $response['statusMessage'] = "The Change Password API failed";
                        }
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