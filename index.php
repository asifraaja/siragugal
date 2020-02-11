<?php
    header("Access-Control-Allow-Origin", "*");
    header("Content-Type", "application/json");
    header("Accept", "application/json");

    include_once './config/Database.php';
    include_once './models/User.php';

    $database = new Database();
    $db = $database->connect();

    $user = new User($db);
    $result = $user->findOneUser("123");
    
    $num = $result->rowcount();
    $response = array();
    if($num > 0){
        $response['users'] = array();

        while($row = $result->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            $user = array(
                'id' => $id,
                'username' => $username
            );
            array_push($response['users'], $user);
        }

        echo json_encode($response);
    }else{
        echo json_encode(
            array(
                'error' => 'No data'
            )
        );
    }

?>