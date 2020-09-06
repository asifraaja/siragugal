<?php
    class Properties{
        private $props = array(
            "TEXTLOCALEMAIL" => "reebas7@gmail.com",
            "TEXTLOCALAPIKEY" => "+8QzzVOQ65g-P8RO2iMq0GR7XDaR4yKAyvkjqYvQW4",
            "TEXTLOCALHASH" => "2a21f3d9da52f0b467276793f3d949e024118339d77f700fbc58f94ed641d798",
            "TEXTLOCALSENDER" => "SIRAGU",
            "TEXTLOCALTEMPLATE" => "Thank you for registering with Siragugal trust. Your OTP code is ?. OTP is valid for 10 minutes.",
            "RESENDTEMPLATE" => "Your OTP code for reset password is ?. OTP is valid for 10 minutes.",
            "VERIFYTEMPLATE" => "Your Re-verification OTP code is ?. OTP is valid for 10 minutes",
            "OTPCOST" => 9,
            "PASSWORDCOST" => 11,
            "OTPMINIMUM" => 100000,
            "OTPMAXIMUM" => 999999,
            "LOCAL_USERNAME" => "fdsa",
            "LOCAL_PASSWORD" => "DSFA",
            "PROD_USERNAME" => "siragugal",
            "PROD_PASSWORD" => "HbU1whFuM@6l"
        ); 

        public function getProps(){
            return $this->props;
        }
    }
?>