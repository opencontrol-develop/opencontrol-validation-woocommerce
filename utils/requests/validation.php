<?php

class Validation 
{
    const SECONDS_TO_MILISECONDS = 1000;
    const ENDPOINT = '/v1/validation';   
    const REQUIRED_FIELDS = [
        'id',
        'session_id',
        'amount',
        'transaction_date',
        'payment',
        'merchant',
    ];
    
    public $id;
    public $session_id;
    public $amount;
    public $currency = "MXN";
    public $transaction_date;
    public $order_id;
    public $source = "API";
    public $reason = "00"; //Validación
    public $validation_type = "COMPLETE";
    public $customer;
    public $products;
    public $payment;
    public $shipping;
    public $billing;
    public $merchant;

    public function __construct() {
        $this->transaction_date = round(microtime(true) * self::SECONDS_TO_MILISECONDS);
        $this->products = [];
        $this->customer = new Customer();
        $this->payment = new Payment();
        $this->shipping = new Address();
        $this->billing = new Address();
        $this->merchant = new Merchant();
    }
}