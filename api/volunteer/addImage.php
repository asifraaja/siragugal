<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Methods: POST');
    header('Content-Type: application/json');
    header('Accept: application/json');

    include_once '../../models/User.php';
    include_once '../../config/SingletonDB.php';

    $response = array();

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $database = SingletonDB::getInstance();
        $db = $database->getConnection();

        try{
            $targetDir = "/Applications/XAMPP/xamppfiles/temp/";
            $maxsize    = 2097152;

            $userId = $_POST['userId'];
            $photoFile = $targetDir.basename($_FILES['photo']['name']);
            $signatureFile = $targetDir.basename($_FILES['signature']['name']);
            $addressProofFile = $targetDir.basename($_FILES['addressProof']['name']);

            $photoExtension = strtolower(pathinfo($photoFile,PATHINFO_EXTENSION));
            $signatureExtension = strtolower(pathinfo($signatureFile,PATHINFO_EXTENSION));
            $addressProofExtension = strtolower(pathinfo($addressProofFile,PATHINFO_EXTENSION));

                $targetPhoto = $targetDir.$userId.'-'.'photo';
                $targetSignature = $targetDir.$userId.'-'.'signature';
                $targetAddressProof = $targetDir.$userId.'-'.'addressProof';

                $error = array();
                if($_FILES['photo']['size'] >= $maxsize){
                    $error['errorCode'] = 'MAX_SIZE_EXCEEDED';
                    $error['errorMessage'] = 'Your Photo Size should not exceed 2 MB';
                }
                if($_FILES['signature']['size'] >= $maxsize){
                    $error['errorCode'] = 'MAX_SIZE_EXCEEDED';
                    $error['errorMessage'] = 'Your Signature Size should not exceed 2 MB';
                }
                if($_FILES['addressProof']['size'] >= $maxsize){
                    $error['errorCode'] = 'MAX_SIZE_EXCEEDED';
                    $error['errorMessage'] = 'Your Address Proof Size should not exceed 2 MB';
                }

                if($error != null){
                    $response['error'] = $error;
                }else{
                    if(move_uploaded_file($_FILES['photo']['tmp_name'], $targetPhoto)){
                        $response['isPhoto'] = '1';
                    }else{
                        $error['errorCode'] = 'PHOTO_ERROR';
                    }
                    if(move_uploaded_file($_FILES['signature']['tmp_name'], $targetSignature)){
                        $response['isSignature'] = '1';
                        $user = new User($db);
                        $user->updateVolunteerStatus($userId, 'Y');
                    }else{
                        $error['errorCode'] = 'SIGNATURE_ERROR';
                    }
                    if(move_uploaded_file($_FILES['addressProof']['tmp_name'], $targetAddressProof)){
                        $response['isAddressProof'] = '1';
                    }else{
                        $error['errorCode'] = 'PHOTO_ERROR';
                    }

                    if($error == null) {
                        $user = new User($db);
                        $user->updateVolunteerStatus($userId, 'Y');
                    }else{
                        $response['error'] = $error;
                    }
                }
        }catch(Exception $e){
            echo 'Exception occured '.$e;
        }finally{
            // $database->disconnect();
        }
        echo json_encode($response);
    }
?>