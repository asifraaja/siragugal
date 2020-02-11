<?php

class Queries{
    public $queries = array(
        "findOneUserQuery" => "select count(mail_id) as count from user_details where ph_no = ?",
        "createUserDetailsQuery" => "insert into user_details(firstname,lastname,ph_no,mail_id,dob) values(?,?,?,?,?)",
        "createLoginDetailsQuery" => "insert into login_details(usr_id,username,password,last_login_dttm) values(?,?,?,?)",
        "updateLoginDetailsQuery" => "update login_details set last_login_dttm=?,num_of_tries=0 where usr_id=?",
        "viewUserQuery" => "select usr_id from user_details where ph_no = ?",
        "viewUserAllQuery" => "select ud.usr_id as usr_id, firstname, lastname, ph_no, mail_id, dob, username, last_login_dttm, admin_fl, vol_fl from user_details ud, login_details ld where ud.usr_id=ld.usr_id and ud.usr_id=?",
        "loginUserQuery" => "select * from login_details where username = ? or password = ? and is_locked != 'Y'",
        "deleteFromUserDetailsQuery" => "delete from user_details where usr_id=?",
        "updatePasswordQuery" => "update login_details set password = ? where usr_id = ?",
        "addCardQuery" => "insert into card_details(usr_id,cardNumber,cardName,cartMonth,cardYear,cardCVV) values(?,?,?,?,?,?)",
        "selectCardQuery" => "select card_id, cardNumber, cardName, cardMonth, cardYear from card_details where usr_id = ?",
        "getTriesCountQuery" => "select num_of_tries,is_locked from login_details where username = ?",
        "updateTriesCountQuery" => "update login_details set num_of_tries = ? where username = ?",
        "lockTheUserQuery" => "update login_details set num_of_tries = ?, is_locked = ? where username = ?",
        "addVolunteerContactQuery" => "INSERT INTO vol_contact (usr_id, mail_id, ph_no, whatsapp_no) VALUES (?,?,?,?);"
    );

    public function getQueries(){
        return $this->queries;
    }
}

?>