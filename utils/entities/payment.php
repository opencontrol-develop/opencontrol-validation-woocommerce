<?php

class Payment
{
    const REQUIRED_FIELDS = [
        'card'
    ];
    
    public $channel = '9'; //Comercio eléctronico

    public $card;

    public $address;

    public function __construct() {
        $this->card = new Card();
        $this->address = new Address();
    }
}