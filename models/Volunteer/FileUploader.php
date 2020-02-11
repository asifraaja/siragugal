<?
    class FileUploader{
        public function isValidFile($fileExtension){
            $isValid = 1;
            if($fileExtension != "jpg" && $fileExtension != "png" && $fileExtension != "jpeg"
                && $fileExtension != "gif" ) {
                 $isValid = 0;   
            }
            return $isValid;
        }

        public function isValidFolder($folder){
            echo 'folder:'.$folder;
            if(!is_dir($folder)) mkdir($folder, '755', true);
        }
    }

?>