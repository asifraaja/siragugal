----------------- USER_DETAILS TABLE------------------
CREATE TABLE IF NOT EXISTS user_details (
  usr_id INT(10) NOT NULL AUTO_INCREMENT,
  firstname VARCHAR(255) NOT NULL,
  lastname VARCHAR(255) DEFAULT NULL,
  ph_no VARCHAR(255) NOT NULL,
  mail_id VARCHAR(255) NOT NULL,
  dob DATE NOT NULL,
  UNIQUE (ph_no),
  PRIMARY KEY (usr_id)
) ENGINE=InnoDB



----------------- LOGIN_DETAILS TABLE------------------
CREATE TABLE IF NOT EXISTS login_details (
  login_id INT(10) NOT NULL AUTO_INCREMENT,
  usr_id  INT(10) NOT NULL,
  username VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  last_login_dttm TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  admin_fl char(1) DEFAULT 'N',
  vol_fl char(1) DEFAULT 'N',
  otp_fl char(1) DEFAULT 'N',
  num_of_tries INT(1) DEFAULT 0,
  is_locked char(1) DEFAULT 'N',
  PRIMARY KEY (usr_id),
  FOREIGN KEY (usr_id) REFERENCES user_details(usr_id)
) ENGINE=InnoDB


---------------------------------------------------- VOLUNTEERS------------------------------------------------------------------------

----------------- VOL_CONTACT TABLE------------------
CREATE TABLE IF NOT EXISTS vol_contact (
  vol_con_id INT(10) NOT NULL AUTO_INCREMENT,
  usr_id  INT(10) NOT NULL,
  mail_id VARCHAR(255) NOT NULL,
  ph_no VARCHAR(255) NOT NULL,
  whatsapp_no VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (vol_con_id),
  FOREIGN KEY (usr_id) REFERENCES user_details(usr_id)
) ENGINE=InnoDB

----------------- VOL_PERSONAL_INFO TABLE------------------
CREATE TABLE IF NOT EXISTS vol_personal_info (
  vol_pi_id INT(10) NOT NULL AUTO_INCREMENT,
  usr_id  INT(10) NOT NULL,
  name VARCHAR(255) DEFAULT NULL,
  gender CHAR(5),
  dob VARCHAR(20) DEFAULT NULL,
  blood_group VARCHAR(10),
  father_name VARCHAR(255),
  mother_name VARCHAR(255),
  PRIMARY KEY (vol_pi_id),
  FOREIGN KEY (usr_id) REFERENCES user_details(usr_id)
) ENGINE=InnoDB

----------------- VOL_ADDRESS TABLE------------------
CREATE TABLE IF NOT EXISTS vol_address (
  vol_adr_id INT(10) NOT NULL AUTO_INCREMENT,
  usr_id  INT(10) NOT NULL,
  native_state VARCHAR(255) NOT NULL,
  native_district VARCHAR(255) DEFAULT NULL,
  native_region VARCHAR(255) DEFAULT NULL,
  permanent_address VARCHAR(255) DEFAULT NULL,
  permanent_district VARCHAR(255) DEFAULT NULL,
  permanent_state VARCHAR(255) DEFAULT NULL,
  permanent_pincode INT(10) DEFAULT NULL,
  curr_address VARCHAR(255) DEFAULT NULL,
  curr_district VARCHAR(255) DEFAULT NULL,
  curr_state VARCHAR(255) DEFAULT NULL,
  curr_pincode INT(10) DEFAULT NULL,
  PRIMARY KEY (vol_adr_id),
  FOREIGN KEY (usr_id) REFERENCES user_details(usr_id)
) ENGINE=InnoDB

----------------- VOL_EDUCATION TABLE------------------
CREATE TABLE IF NOT EXISTS vol_education (
  vol_edu_id INT(10) NOT NULL AUTO_INCREMENT,
  usr_id  INT(10) NOT NULL,
  degree VARCHAR(10) NOT NULL,
  iti_institute VARCHAR(255) DEFAULT NULL,
  iti_place VARCHAR(255) DEFAULT NULL,
  iti_cmp_year INT(10) DEFAULT NULL,
  iti_course VARCHAR(255) DEFAULT NULL,
  iti_branch VARCHAR(255) DEFAULT NULL,
  ug_institute VARCHAR(255) DEFAULT NULL,
  ug_place VARCHAR(255) DEFAULT NULL,
  ug_cmp_year INT(10) DEFAULT NULL,
  ug_course VARCHAR(255) DEFAULT NULL,
  ug_branch VARCHAR(255) DEFAULT NULL,
  pg_institute VARCHAR(255) DEFAULT NULL,
  pg_place VARCHAR(255) DEFAULT NULL,
  pg_cmp_year INT(10) DEFAULT NULL,
  pg_course VARCHAR(255) DEFAULT NULL,
  pg_branch VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (vol_edu_id),
  FOREIGN KEY (usr_id) REFERENCES user_details(usr_id)
) ENGINE=InnoDB
