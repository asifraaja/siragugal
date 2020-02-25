<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Methods: GET');
    header('Content-Type: application/json');
    header('Accept: application/json');

    include_once '../../config/Database.php';
    include_once '../../models/User.php';

    error_reporting(E_ALL); 
    ini_set("display_errors", 1); 

    if($_SERVER['REQUEST_METHOD'] = 'GET'){
        $database = new Database();
        $db = $database->connect();

        $user = new User($db);

        try{
            $users = $user->getAllVolunteers();

            $response = array();
            $response['status'] = 'OK';
            $response['users'] = $users;
        }catch(Exception $e){
            echo "Exception occured <br>" .$e;
        }finally{
            $db->close();
        }
        echo json_encode($response);
    }
?>