<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Methods: POST');
    header('Content-Type: application/json');
    header('Accept: application/json');

    include_once '../../config/Database.php';
    include_once '../../config/Validator.php';
    include_once '../../models/Volunteer/Contact.php';

    $validator = new Validator();
    $database = new Database();
    $response = array();

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $db = $database->connect();
        $contact = new Contact($db);

        try{
            $data = json_decode(file_get_contents("php://input"));
            $userId = '';

            if(isset($data->phoneNumber) && $validator->isValidPhoneNumber($data->phoneNumber)) $phoneNumber = $data->phoneNumber;
            else $error = 'Phone Number must be 10 digit number.';
            
            if(isset($data->whatsappNumber) && $validator->isValidPhoneNumber($data->whatsappNumber)) $whatsappNumber = $data->whatsappNumber;
            else $error = 'Whatsapp Number must be 10 digit number.';

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
                /**
                 * If user has already filled the contact form Then DELETE the existing data and ADD new data.
                 * Else ADD the new data.
                 */
                if(isset($data->emailId)) $emailId = $data->emailId;
                else $email = '';

                // $userId = $contact->isContactAdded($userId);
                $res = $contact->updateContact($userId, $phoneNumber, $whatsappNumber, $emailId);
                
                $response = $res;
                if($res['error'] == '0'){
                    // Contact is added/updated successfully
                    $contact = array();
                    $contact['userId'] = $userId;
                    $contact['phoneNumber'] = $phoneNumber;
                    $contact['whatsappNumber'] = $whatsappNumber;
                    $contact['emailId'] = $emailId;
                    $response['statusCode'] = '0';
                    $response['contactInfo'] = $contact;
                }
            }
        }catch(Exception $e){

        }finally{
            $db->close();
        }
        echo json_encode($response);
    }
?>