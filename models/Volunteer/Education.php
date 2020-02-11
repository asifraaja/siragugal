<?php
    include_once '../../config/Properties.php';
    include_once '../../config/Validator.php';

    class Education{

        private $conn;
        private $props;
        /**
         * constructor for User
         * @param db - database connection
         */
        public function __construct($db){
            $this->conn = $db;
            $properties = new Properties();
            $this->props = $properties->getProps();
        }

        /**
         * UPDATE THE EDUCATION DETAILS FOR A PARTICULAR USERID
         */
        public function updateEducation($userId, $degree, 
                $itiInstitute, $itiPlace, $itiCmpYear, $itiCourse, $itiBranch,
                $ugInstitute, $ugPlace, $ugCmpYear, $ugCourse, $ugBranch,
                $pgInstitute, $pgPlace, $pgCmpYear, $pgCourse, $pgBranch){
            $res = array();
            // 1. Delete the existing Education Details
            $query = "delete from vol_education where usr_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('d', $userId);
            $delete_education_result = $stmt->execute();
            if($delete_education_result == false) return self::setError('DELETE_EDUCATION_ERROR');
            // 2. Insert the Education Details
            $res = self::addEducation($userId, $degree, 
                $itiInstitute, $itiPlace, $itiCmpYear, $itiCourse, $itiBranch,
                $ugInstitute, $ugPlace, $ugCmpYear, $ugCourse, $ugBranch,
                $pgInstitute, $pgPlace, $pgCmpYear, $pgCourse, $pgBranch);
            return $res;
        }

        /**
         * INSERT EDUCATION DETAILS
         */
        public function addEducation($userId, $degree, 
                    $itiInstitute, $itiPlace, $itiCmpYear, $itiCourse, $itiBranch,
                    $ugInstitute, $ugPlace, $ugCmpYear, $ugCourse, $ugBranch,
                    $pgInstitute, $pgPlace, $pgCmpYear, $pgCourse, $pgBranch){
            $res = array();
            // 1. Add Education Details
            $query = "INSERT INTO vol_education (usr_id, degree, iti_institute, iti_place, iti_cmp_year, iti_course, iti_branch,
                    ug_institute, ug_place, ug_cmp_year, ug_course, ug_branch,
                    pg_institute, pg_place, pg_cmp_year, pg_course, pg_branch) VALUES (?,?, ?,?,?,?,?, ?,?,?,?,?, ?,?,?,?,?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('dsssdssssdssssdss', $userId, $degree, $itiInstitute, $itiPlace, $itiCmpYear, $itiCourse, $itiBranch, $ugInstitute, $ugPlace, $ugCmpYear, $ugCourse, $ugBranch, $pgInstitute, $pgPlace, $pgCmpYear, $pgCourse, $pgBranch);
            $insert_education_result = $stmt->execute();
            if($insert_education_result == false) return self::setError('INSERT_EDUCATION_ERROR');
            $res['error'] = '0';
            $res['errorCode'] = '';
            $res['errorMessage'] = '';
            $res['education'] = self::setEducationInfo($userId, $degree, 
                        $itiInstitute, $itiPlace, $itiCmpYear, $itiCourse, $itiBranch,
                        $ugInstitute, $ugPlace, $ugCmpYear, $ugCourse, $ugBranch,
                        $pgInstitute, $pgPlace, $pgCmpYear, $pgCourse, $pgBranch);
            return $res;
        }

        /**
         * Set the Error Code
         */
        public function setError($errorCode){
            $res = array();
            $res['error'] = '-1';
            $res['errorCode'] = $errorCode;
            $res['errorMessage'] = 'Something went wrong while adding Address Details. We\'ll fix it ASAP';
            return $res;
        }

        /**
         * VALIDATE IF USER ALREADY EXISTS
         */
        public function userExists($userId){
            $query = "SELECT * FROM user_details WHERE usr_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('d', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows == 0) return 0;
            $row = $result->fetch_assoc();
            return $row['usr_id'];
        }

        /**
         * Return Education Details For a User
         */
        public function getEducationInfo($userId){
            $query = "SELECT * FROM vol_education WHERE usr_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('d', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $res = self::setEducationInfo($row['usr_id'],$row['degree'],
                    $row['iti_institute'],$row['iti_place'],$row['iti_cmp_year'],$row['iti_course'],$row['iti_branch'],
                    $row['ug_institute'],$row['ug_place'],$row['ug_cmp_year'],$row['ug_course'],$row['ug_branch'],
                    $row['pg_institute'],$row['pg_place'],$row['pg_cmp_year'],$row['pg_course'],$row['pg_branch']);
            return $res;
        }

        /**
         * Set the Education Details
         */
        public function setEducationInfo($userId, $degree, 
                    $itiInstitute, $itiPlace, $itiCmpYear, $itiCourse, $itiBranch,
                    $ugInstitute, $ugPlace, $ugCmpYear, $ugCourse, $ugBranch,
                    $pgInstitute, $pgPlace, $pgCmpYear, $pgCourse, $pgBranch){
            $res = array();
            
            $res['userId'] = $userId;
            $res['degree'] = $degree;

            $res['itiInstitute'] = $itiInstitute;
            $res['itiPlace'] = $itiPlace;
            $res['itiCmpYear'] = $itiCmpYear;
            $res['itiCourse'] = $itiCourse;
            $res['itiBranch'] = $itiBranch;

            $res['ugInstitute'] = $ugInstitute;
            $res['ugPlace'] = $ugPlace;
            $res['ugCmpYear'] = $ugCmpYear;
            $res['ugCourse'] = $ugCourse;
            $res['ugBranch'] = $ugBranch;

            $res['pgInstitute'] = $pgInstitute;
            $res['pgPlace'] = $pgPlace;
            $res['pgCmpYear'] = $pgCmpYear;
            $res['pgCourse'] = $pgCourse;
            $res['pgBranch'] = $pgBranch;
            
            return $res;
        }
    }
?>