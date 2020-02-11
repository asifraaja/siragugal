<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Methods: POST');
    header('Content-Type: application/json');
    header('Accept: application/json');

    $response = array();

    if($_SERVER['REQUEST_METHOD'] == 'POST'){

        try{
            $targetDir = "/Applications/XAMPP/xamppfiles/temp/";
            $maxsize    = 2097152;

            $userId = $_POST['userId'];
            $photoFile = $targetDir.basename($_FILES['photo']['name']);
            $signatureFile = $targetDir.basename($_FILES['signature']['name']);

            $photoExtension = strtolower(pathinfo($photoFile,PATHINFO_EXTENSION));
            $signatureExtension = strtolower(pathinfo($signatureFile,PATHINFO_EXTENSION));

            // $isValidPhoto = $fu->isValidFile($photoExtension);
            // $isValidSignature = $fu->isValidFile($signatureExtension);

            // if($isValidPhoto == 1 && $isValidSignature == 1){
                // $fu->isValidFolder($targetDir);
                $targetPhoto = $targetDir.$userId.'-'.'photo';
                $targetSignature = $targetDir.$userId.'-'.'signature';

                if($_FILES['photo']['size'] >= $maxsize || $_FILES['signature']['size'] >= $maxsize){
                    $error['errorCode'] = 'MAX_SIZE_EXCEEDED';
                    $error['errorMessage'] = 'Size of each photo must not exceed 2 MB.';
                }else{
                    if(move_uploaded_file($_FILES['photo']['tmp_name'], $targetPhoto)){
                        $response['isPhoto'] = '1';
                    }else{
                        $error['errorCode'] = 'PHOTO_ERROR';
                        echo 'error';
                    }
                    if(move_uploaded_file($_FILES['signature']['tmp_name'], $targetSignature)){
                        $response['isSignature'] = '1';
                    }else{
                        $error['errorCode'] = 'SIGNATURE_ERROR';
                        echo 'error';
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