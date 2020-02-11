<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Methods: POST');
    header('Content-Type: application/json');
    header('Accept: application/json');

    include_once '../../config/SingletonDB.php';
    include_once '../../config/Validator.php';
    include_once '../../models/Volunteer/PersonalInfo.php';
    include_once '../../models/Volunteer/Contact.php';

    $validator = new Validator();
    $database = SingletonDB::getInstance();
    $response = array();

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $db = $database->getConnection();
        $info = new PersonalInfo($db);
        $contact = new Contact($db);

        try{
            $data = json_decode(file_get_contents("php://input"));
            $userId = '';

            if(isset($data->phoneNumber) && $validator->isValidPhoneNumber($data->phoneNumber)) $phoneNumber = $data->phoneNumber;
            else $error = 'Phone Number must be 10 digit number.';
            
            if(isset($data->whatsappNumber) && $validator->isValidPhoneNumber($data->whatsappNumber)) $whatsappNumber = $data->whatsappNumber;
            else $error = 'Whatsapp Number must be 10 digit number.';

            if(isset($data->emailId)) $emailId = $data->emailId;
            else $email = '';

            if(isset($data->gender)) $gender = strtoupper($data->gender);
            else $gender = 'N';

            if(isset($data->bloodGroup)) $bloodGroup = $data->bloodGroup;
            else $bloodGroup = 'NA';

            if(isset($data->fatherName)) $fatherName = $data->fatherName;
            else $fatherName = 'NA';

            if(isset($data->motherName)) $motherName = $data->motherName;
            else $motherName = 'NA';
            
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
                $piRes = $info->updatePersonalInfo($userId, $gender, $bloodGroup, $fatherName, $motherName, $dob);
                $contactRes = $contact->updateContact($userId, $phoneNumber, $whatsappNumber, $emailId);
                
                echo 'PI : '.$piRes['error'];
                echo 'Contact : '.$contactRes['error'];
                if($piRes['error'] == '0' && $contactRes['error'] == '0'){
                    // Contact is added/updated successfully
                    $response = $piRes;
                    $response = $contactRes;
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