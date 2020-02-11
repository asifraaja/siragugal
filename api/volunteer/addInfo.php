<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Methods: POST');
    header('Content-Type: application/json');
    header('Accept: application/json');

    include_once '../../config/SingletonDB.php';
    include_once '../../config/Validator.php';
    include_once '../../models/Volunteer/PersonalInfo.php';

    $validator = new Validator();
    $database = SingletonDB::getInstance();
    $response = array();

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $db = $database->getConnection();
        $info = new PersonalInfo($db);

        try{
            $data = json_decode(file_get_contents("php://input"));
            $userId = '';

            if(isset($data->gender) && $data->gender!='') $gender = strtoupper($data->gender);
            else $error = 'All fields must be filled. You forgot to fill Gender.';

            if(isset($data->bloodGroup) && $data->bloodGroup!='') $bloodGroup = $data->bloodGroup;
            else $error = 'All fields must be filled. You forgot to fill Blood Group.';

            if(isset($data->fatherName) && $data->fatherName!='') $fatherName = $data->fatherName;
            else $error = 'All fields must be filled. You forgot to fill Father Name.';

            if(isset($data->motherName) && $data->motherName!='') $motherName = $data->motherName;
            else $error = 'All fields must be filled. You forgot to fill Mother Name.';

            if(isset($data->dob) && $data->dob!='') $dob = $data->dob;
            else $error = 'All fields must be filled. You forgot to fill Date Of Birth.';
            
            if(isset($data->userId)){
                if($info->userExists($data->userId))
                    $userId = $data->userId;
                else
                    $error = 'No such user exists';
            }else $error = 'User Id must be passed';

            if(!empty($error)){
                $response['statusCode'] = '-1';
                $response['errorCode'] = 'PARAMS_MISSING';
                $response['errorMessage'] = $error;
            }else{
                /**
                 * If user has already filled the personalInfo form Then DELETE the existing data and ADD new data.
                 * Else ADD the new data.
                 */
                $res = $info->updatePersonalInfo($userId, $gender, $bloodGroup, $fatherName, $motherName, $dob);
                
                $response = $res;
                if($res['error'] == '0'){
                    // Contact is added/updated successfully
                    $response = $res;
                    $response['statusCode'] = '0';
                }
            }
        }catch(Exception $e){
            echo 'Exception occured '.$e;
        }finally{
            $database->disconnect();
        }
        echo json_encode($response);
    }
?>