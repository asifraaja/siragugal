<?php
class LoginRequest
{
	public $username;
	public $password;

	public function __construct($json = false) {
        if ($json) $this->set(json_decode($json, true));
    }

    public function set($data) {
        foreach ($data AS $key => $value) {
            $this->{$key} = $value;
        }
    }
}
?>