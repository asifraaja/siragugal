<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
    header('Content-Type: application/json');
    header('Accept: application/json');

    include_once '../../config/Database.php';
    include_once '../../models/User.php';
    include_once '../../config/Validator.php';

    error_reporting(E_ALL); 
    ini_set("display_errors", 1); 
    /**
     * Logs a user into siragugaltrust.in
     * 1. When user enters Username & Password. 
     * 2. If both are valid & user is already verified then -> user logins & user details are sent back.
     * 3. If both are valid but user is not verified then -> verification OTP is sent to the user.
     * 4. If one of them fails then -> error is thrown
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
        if(isset($data->username) && $validator->isValidNumber($data->username))
            $username = $data->username;
        else
            $error = $error . "Username cannot be empty or alphabetical";
        if(isset($data->password) && !empty($data->password))
            $password = $data->password;
        else
            $error = $error . "Password cannot be empty";
        
        if(!empty($error)){
            $response['statusCode'] = "-1";
            $response['statusMessage'] = "The Login API did not execute successfully";
            $response['errorCode'] = "PARAM_MISSING";
            $response['errorMessage'] = $error;
        }else{
            $response = $user->loginUser($username, $password);     // login for a user
            if($response['errorCode'] == "0"){
                // login successfull
                $response['statusCode'] = "0";
                $response['statusMessage'] = "The Login API is executed successfully";
            }else if($response['errorCode'] == "OTP_NEEDED"){
                // if the verification OTP was not entered
                $maskedContact = $user->maskContact($response['user']['phoneNumber']);
                $response['statusCode'] = "-1";
                $response['statusMessage'] = "OTP has been sent to your registered PhoneNumber: ".$maskedContact;
            }else{
                // login failed
                $response['statusCode'] = "-1";
                $response['statusMessage'] = "The Login API is not executed successfully";
            }
        }
    }catch(Exception $e){
        echo "Exception occured <br>" .$e;
    }finally{
        $db->close();
    }

    echo json_encode($response);
    }
?>