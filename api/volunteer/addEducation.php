<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Methods: POST');
    header('Content-Type: application/json');
    header('Accept: application/json');

    include_once '../../config/SingletonDB.php';
    include_once '../../config/Validator.php';
    include_once '../../models/Volunteer/Education.php';
    include_once '../../models/User.php';

    $validator = new Validator();
    $database = SingletonDB::getInstance();
    $response = array();

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $db = $database->getConnection();
        $education = new Education($db);
        $error = '';

        try{
            $data = json_decode(file_get_contents("php://input"));
            /** Check if user already exists */
            if(isset($data->userId)){
                if($education->userExists($data->userId))
                    $userId = $data->userId;
                else
                    $error = 'No such user exists.';
            }else $error = 'User Id must be passed.';

            if(isset($data->degree)) $degree = strtoupper($data->degree);
            else $degree = "EMPTY";

            if(isset($data->itiInstitute)) $itiInstitute = $data->itiInstitute;
            if(isset($data->itiPlace)) $itiPlace = $data->itiPlace;
            if(isset($data->itiCmpYear)){
                if($validator->isValidText($data->itiCmpYear) == false)
                    $itiCmpYear = $data->itiCmpYear;
                else $error = 'ITI Year must be 4 digit year';
            }
            if(isset($data->itiCourse)) $itiCourse = $data->itiCourse;
            if(isset($data->itiBranch)) $itiBranch = $data->itiBranch;

            if(isset($data->ugInstitute)) $ugInstitute = $data->ugInstitute;
            if(isset($data->ugPlace)) $ugPlace = $data->ugPlace;
            if(isset($data->ugCmpYear)){
                if($validator->isValidText($data->ugCmpYear) == false) $ugCmpYear = $data->ugCmpYear;
                else $error = 'UG Year must be 4 digit year';
            }
            if(isset($data->ugCourse)) $ugCourse = $data->ugCourse;
            if(isset($data->ugBranch)) $ugBranch = $data->ugBranch;

            if(isset($data->pgInstitute)) $pgInstitute = $data->pgInstitute;
            if(isset($data->pgPlace)) $pgPlace = $data->pgPlace;
            if(isset($data->pgCmpYear)){
                if($validator->isValidText($data->pgCmpYear) == false)
                    $pgCmpYear = $data->pgCmpYear;
                else $error = 'PG Year must be 4 digit year';
            }
            if(isset($data->pgCourse)) $pgCourse = $data->pgCourse;
            if(isset($data->pgBranch)) $pgBranch = $data->pgBranch;

            if(!empty($error)){
                $response['statusCode'] = '-1';
                $response['errorCode'] = 'PARAMS_MISSING';
                $response['errorMessage'] = $error;
            }else{
                    /** VALIDATE THE DEGREE
                     * IF degree == ITI, then atleast ITI data must be present
                     * IF degree == UG, then atleast UG data must be present
                     * IF degree == PG, then atlease PG data must be present
                     */
                    if(strcmp($degree, 'ITI') == 0){
                        $isValid = $validator->isPresentWithValue($data->itiInstitute, $data->itiPlace, $data->itiCmpYear, $data->itiCourse, $data->itiBranch);
                        if(!$isValid) 
                            $error = $error.'ITI Details Must be Filled. Completion Year(YYYY) only needs to be filled.';
                    }else if(strcmp($degree, 'UG') == 0){
                        $isValid = $validator->isPresentWithValue($data->ugInstitute, $data->ugPlace, $data->ugCmpYear, $data->ugCourse, $data->ugBranch);
                        if(!$isValid)
                            $error = $error.'UG Details Must be Filled. Completion Year(YYYY) only needs to be filled.';
                    }else if(strcmp($degree, 'PG') == 0){
                        $isValid = $validator->isPresentWithValue($data->pgInstitute, $data->pgPlace, $data->pgCmpYear, $data->pgCourse, $data->pgBranch);
                        echo 'isvalid : '.$isValid;
                        if(!$isValid)
                            $error = $error.'PG Details Must be Filled. Completion Year(YYYY) only needs to be filled.';
                    }else
                        $error = $error.'Degree Must be ITI, UG or PG';

                    /** Biz Implementation */
                    if(!empty($error)){
                        $response['statusCode'] = '-1';
                        $response['errorCode'] = 'PARAMS_MISSING';
                        $response['errorMessage'] = $error;
                    }else{
                    $res = $education->updateEducation($userId, $degree, 
                                $itiInstitute, $itiPlace, $itiCmpYear, $itiCourse, $itiBranch,
                                $ugInstitute, $ugPlace, $ugCmpYear, $ugCourse, $ugBranch,
                                $pgInstitute, $pgPlace, $pgCmpYear, $pgCourse, $pgBranch);

                        $response = $res;
                        if($res['error'] == '0'){
                            // Address is added/updated successfully
                            $response = $res;
                            $response['statusCode'] = '0';
                            // Update the volunteer status
                            $user = new User($db);
                            $user->updateVolunteerStatus($userId, 'Y');
                        }   
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