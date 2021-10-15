<?php
class Auth{
    public $user;
    public $password;

    public function toBase64() {
        return base64_encode($this->user.':'.$this->password);
    }
}