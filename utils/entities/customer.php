<?php

class Customer 
{
    const REQUIRED_FIELDS = [
        'id',
        'name',
        'last_name',
        'phone',
        'email',
    ];

    public $id;
    public $name;
    public $last_name;
    public $phone;
    public $email;
}