<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Methods: POST');
    header('Content-Type: application/json');
    header('Accept: application/json');

    include_once '../../config/SingletonDB.php';
    include_once '../../config/Validator.php';
    include_once '../../models/Volunteer/Address.php';

    $validator = new Validator();
    $database = SingletonDB::getInstance();
    $response = array();

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $db = $database->getConnection();
        $address = new Address($db);

        try{
            $data = json_decode(file_get_contents("php://input"));
            $error = '';

            /** Native Address Details */
            if(isset($data->nativeState) && !$validator->isEmpty($data->nativeState)) $nativeState = $data->nativeState;
            else $error = $error.'Native State cannot be null or empty.';

            if(isset($data->nativeDistrict) && !$validator->isEmpty($data->nativeDistrict)) $nativeDistrict = $data->nativeDistrict;
            else $error = $error.'Native District cannot be null or empty.';

            if(isset($data->nativeRegion) && !$validator->isEmpty($data->nativeRegion)) $nativeRegion = $data->nativeRegion;
            else $error = $error.'Native Region cannot be null or empty.';

            /*if(isset($data->nativePincode)) $nativePincode = $data->nativePincode;
            else $error = 'Native Pincode cannot be null or empty';*/

            /** Permanent Address Details */
            if(isset($data->permanentAddress) && !$validator->isEmpty($data->permanentAddress)) $permanentAddress = $data->permanentAddress;
            else $error = $error.'Permanent Address cannot be null or empty.';

            if(isset($data->permanentDistrict) && !$validator->isEmpty($data->permanentDistrict)) $permanentDistrict = $data->permanentDistrict;
            else $error = $error.'Permanent District cannot be null or empty.';

            if(isset($data->permanentState) && !$validator->isEmpty($data->permanentState)) $permanentState = $data->permanentState;
            else $error = $error.'Permanent State cannot be null or empty.';

            if(isset($data->permanentPincode) && $validator->isValidPincode($data->permanentPincode)) $permanentPincode = $data->permanentPincode;
            else $error = $error.'Permanent Pincode must be of 6 digits.';

            /** Current Address Details */
            if(isset($data->currentAddress) && !$validator->isEmpty($data->currentAddress)) $currentAddress = $data->currentAddress;
            else $error = $error.'Current Address cannot be null or empty.';

            if(isset($data->currentDistrict) && !$validator->isEmpty($data->currentDistrict)) $currentDistrict = $data->currentDistrict;
            else $error = $error.'Current District cannot be null or empty.';

            if(isset($data->currentState) && !$validator->isEmpty($data->currentState)) $currentState = $data->currentState;
            else $error = $error.'Current State cannot be null or empty.';

            if(isset($data->currentPincode) && $validator->isValidPincode($data->currentPincode)) $currentPincode = $data->currentPincode;
            else $error = $error.'Current Pincode must be of 6 digits.';
            
            /** Check if user already exists */
            if(isset($data->userId)){
                if($address->userExists($data->userId))
                    $userId = $data->userId;
                else
                    $error = 'No such user exists';
            }else $error = 'User Id must be passed';

            /** Biz Implementation */
            if(!empty($error)){
                $response['statusCode'] = '-1';
                $response['errorCode'] = 'PARAMS_MISSING';
                $response['errorMessage'] = $error;
            }else{
                /**
                 * If user has already filled the Address form Then DELETE the existing data and ADD new data.
                 * Else ADD the new data.
                 */
                $res = $address->updateAddress($userId, $nativeState, $nativeDistrict, $nativeRegion, 
                                $permanentAddress, $permanentDistrict, $permanentState, $permanentPincode, 
                                $currentAddress, $currentDistrict, $currentState, $currentPincode);
                
                $response = $res;
                if($res['error'] == '0'){
                    // Address is added/updated successfully
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