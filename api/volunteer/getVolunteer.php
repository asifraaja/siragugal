<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Methods: POST');
    header('Content-Type: application/json');
    header('Accept: application/json');

    include_once '../../config/SingletonDB.php';
    include_once '../../config/Validator.php';
    include_once '../../models/Volunteer/Education.php';
    include_once '../../models/Volunteer/PersonalInfo.php';
    include_once '../../models/Volunteer/Contact.php';
    include_once '../../models/Volunteer/Address.php';

    $validator = new Validator();
    $database = SingletonDB::getInstance();
    $response = array();

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $db = $database->getConnection();
        $contact = new Contact($db);

        try{
            $data = json_decode(file_get_contents("php://input"));
            $userId = '';

            if(isset($data->userId)){
                if($contact->userExists($data->userId))
                    $userId = $data->userId;
                else
                    $error = 'No such user exists';
            }else $error = 'User Id must be passed';

            if(!empty($error)){
                $response['statusCode'] = '-1';
                $response['errorCode'] = 'PARAMS_MISSING';
                $response['errorMessage'] = $error;
            }else{
                $contact = $contact->getContactInfo($userId);
                
                $info = new PersonalInfo($db);
                $personalInfo = $info->getPersonalInfo($userId);

                $addr = new Address($db);
                $address = $addr->getAddressInfo($userId);
                
                $edu = new Education($db);
                $education = $edu->getEducationInfo($userId);

                $response['statusCode'] = '0';
                $response['contactInfo'] = $contact;
                $response['personalInfo'] = $personalInfo;
                $response['address'] = $address;
                $response['education'] = $education;
            }
        }catch(Exception $e){
            echo 'Exception occured '.$e;
        }finally{
            $database->disconnect();
        }
        echo json_encode($response);
    }
?>