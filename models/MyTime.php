<?php 
    class MyTime{
        public static function generateOTPEndTime(){
            $curr_time = new DateTime(null, new DateTimezone("Asia/Kolkata"));
            $curr_time->add(new DateInterval('PT10M'));
            $end_time = $curr_time->format('Y-m-d H:i:s');
            return $end_time;
        }

        public function generateSessionTimeout(){
            $curr_time = new DateTime();
            $curr_time->add(new DateInterval('PT15M'));
            $end_time = $curr_time->format('Y-m-d H:i:s');
            return $end_time;
        }

        public static function currentTime(){
            $curr_time = new DateTime(null, new DateTimezone("Asia/Kolkata"));
            $curr_time = $curr_time->format('Y-m-d H:i:s');
            return $curr_time;
        }

        public static function getDifference($start_time, $end_time){
            $t1 = new DateTime($start_time, new DateTimezone("Asia/Kolkata"));
            $t2 = new DateTime($end_time, new DateTimezone("Asia/Kolkata"));
            // echo "subtract : ".$end_time." - ".$start_time;
            if($t1 < $t2){
                return "NOT";   // Not Expired
            }else{
                return "YES";   // Expired
            }
        }
    }
?>