<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
    header('Content-Type: application/json');
    header('Accept: application/json');

    include_once '../../config/SingletonDB.php';
    include_once '../../models/User.php';

    error_reporting(E_ALL); 
    ini_set("display_errors", 1); 
    /**
     * Logs a user into siragugaltrust.in
     * 1. When user enters Username & Password. 
     * 2. If both are valid & user is already verified then -> user logins & user details are sent back.
     * 3. If both are valid but user is not verified then -> verification OTP is sent to the user.
     * 4. If one of them fails then -> error is thrown
     */
    if($_SERVER['REQUEST_METHOD'] == 'GET'){
        $database = SingletonDB::getInstance();
        $db = $database->connect();
        $user = new User($db);
        $response = array();
        $error = array();

        try{
        
            $response = $user->testDatabases();
            
        }catch(Exception $e){
            echo "Exception during login: \n".$e; 
        }finally{
            $db->close();
        }

        if(!empty($error)){
            $response['error'] = $error;
            $response['statusCode'] = "-1";
            $response['statusMessage'] = "The Login API Failed.";
        }else{
            $response['statusCode'] = "1";
            $response['statusMessage'] = "The Login API is succesfull.";
        }

        echo json_encode($response);

    }
?>