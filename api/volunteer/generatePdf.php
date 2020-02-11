<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Methods: POST');
    header('Content-Type: application/json');
    header('Accept: application/json');
    // echo 'I m in';

    include_once '../../config/SingletonDB.php';
    include_once '../../config/Validator.php';
    include_once '../../models/Volunteer/Education.php';
    include_once '../../models/Volunteer/PersonalInfo.php';
    include_once '../../models/Volunteer/Contact.php';
    include_once '../../models/Volunteer/Address.php';
    include_once '../../models/User.php';
    // echo 'included db details';

    require '../../vendor/autoload.php';
    
    use Spipu\Html2Pdf\Html2Pdf;
    use Spipu\Html2Pdf\Exception\Html2PdfException;
    use Spipu\Html2Pdf\Exception\ExceptionFormatter;
    
    $validator = new Validator();
    $database = SingletonDB::getInstance();
    $response = array();

    $html2pdf = new Html2Pdf('P', 'A4', 'en', true, 'UTF-8');

    if($_SERVER['REQUEST_METHOD'] == 'GET'){
        $db = $database->getConnection();
        $contact = new Contact($db);

        try{
            $queries = array();
            parse_str($_SERVER['QUERY_STRING'], $queries);
            if(isset($queries['userId'])) $userId = $queries['userId'];
            else $userId = -1;
            
            if($contact->userExists($userId)){}                    
            else $error = 'No such user exists';
            
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

                // echo json_encode($response);

                $text = file_get_contents('./pdfHtmlTemplate.html');
                // personalDetails
                // $text = str_replace("[name]", $response['personalInfo']['name'], $text);
                $text = str_replace("[gender]", $response['personalInfo']['gender'], $text);
                $text = str_replace("[dob]", $response['personalInfo']['dob'], $text);
                $text = str_replace("[fatherName]", $response['personalInfo']['fatherName'], $text);
                $text = str_replace("[motherName]", $response['personalInfo']['motherName'], $text);
                // address
                $text = str_replace("[nativeState]", $response['address']['nativeState'], $text);
                $text = str_replace("[nativeDistrict]", $response['address']['nativeDistrict'], $text);
                $text = str_replace("[nativeRegion]", $response['address']['nativeRegion'], $text);

                $text = str_replace("[permanentAddress]", $response['address']['permanentAddress'], $text);
                $text = str_replace("[permanentDistrict]", $response['address']['permanentDistrict'], $text);
                $text = str_replace("[permanentState]", $response['address']['permanentState'], $text);
                $text = str_replace("[permanentPincode]", $response['address']['permanentPincode'], $text);

                $text = str_replace("[currentAddress]", $response['address']['currentAddress'], $text);
                $text = str_replace("[currentDistrict]", $response['address']['currentDistrict'], $text);
                $text = str_replace("[currentState]", $response['address']['currentState'], $text);
                $text = str_replace("[currentPincode]", $response['address']['currentPincode'], $text);

                // education
                $text = str_replace("[degree]", $response['education']['degree'], $text);

                if($validator->isValidText($response['education']['itiInstitute'])) {
                    $text = str_replace("[iti_institute]", $response['education']['itiInstitute'], $text);
                    $text = str_replace("[iti_place]", $response['education']['itiPlace'], $text);
                    $text = str_replace("[iti_cmp_year]", $response['education']['itiCmpYear'], $text);
                    $text = str_replace("[iti_course]", $response['education']['itiCourse'], $text);
                    $text = str_replace("[iti_branch]", $response['education']['itiBranch'], $text);
                }else{
                    $text = str_replace("[iti_institute]", 'N/A', $text);
                    $text = str_replace("[iti_place]", 'N/A', $text);
                    $text = str_replace("[iti_cmp_year]", 'N/A', $text);
                    $text = str_replace("[iti_course]", 'N/A', $text);
                    $text = str_replace("[iti_branch]", 'N/A', $text);
                }

                if($validator->isValidText($response['education']['ugInstitute'])){
                    $text = str_replace("[ug_institute]", $response['education']['ugInstitute'], $text);
                    $text = str_replace("[ug_place]", $response['education']['ugPlace'], $text);
                    $text = str_replace("[ug_cmp_year]", $response['education']['ugCmpYear'], $text);
                    $text = str_replace("[ug_course]", $response['education']['ugCourse'], $text);
                    $text = str_replace("[ug_branch]", $response['education']['ugBranch'], $text);
                }else{
                    $text = str_replace("[ug_institute]", 'N/A', $text);
                    $text = str_replace("[ug_place]", 'N/A', $text);
                    $text = str_replace("[ug_cmp_year]", 'N/A', $text);
                    $text = str_replace("[ug_course]", 'N/A', $text);
                    $text = str_replace("[ug_branch]", 'N/A', $text);
                }
                
                if($validator->isValidText($response['education']['pgInstitute'])){
                    $text = str_replace("[pg_institute]", $response['education']['pgInstitute'], $text);
                    $text = str_replace("[pg_place]", $response['education']['pgPlace'], $text);
                    $text = str_replace("[pg_cmp_year]", $response['education']['pgCmpYear'], $text);
                    $text = str_replace("[pg_course]", $response['education']['pgCourse'], $text);
                    $text = str_replace("[pg_branch]", $response['education']['pgBranch'], $text);
                }else{
                    $text = str_replace("[pg_institute]", 'N/A', $text);
                    $text = str_replace("[pg_place]", 'N/A', $text);
                    $text = str_replace("[pg_cmp_year]", 'N/A', $text);
                    $text = str_replace("[pg_course]", 'N/A', $text);
                    $text = str_replace("[pg_branch]", 'N/A', $text);
                }

                // contact
                $text = str_replace("[emailId]", $response['contactInfo']['emailId'], $text);
                $text = str_replace("[phoneNumber]", $response['contactInfo']['phoneNumber'], $text);
                $text = str_replace("[whatsappNumber]", $response['contactInfo']['whatsappNumber'], $text); 

                try{
                    $html2pdf->pdf->SetDisplayMode('fullpage');
                    $html2pdf->setDefaultFont('Helvetica');
                    $html2pdf->writeHTML($text);
                    $html2pdf->output('volunteer.pdf');
                }catch(Html2PdfException $pdf){
                    $html2pdf->clean();
                    $formatter = new ExceptionFormatter($e);
                    echo $formatter->getHtmlMessage();
                }
                
            }
        }catch(Exception $e){
            echo 'Exception occured '.$e;
        }finally{
            $database->disconnect();
        }
        
    }
?>