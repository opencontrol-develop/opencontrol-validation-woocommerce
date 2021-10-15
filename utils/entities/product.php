<?php

class Product
{
    const TYPE_OF_PRODUCTS = [
        "PHYSICAL" => "PHYSICAL",
        "DIGITAL" => "DIGITAL",
        "BOTH" => "BOTH"
    ];
    
    public $id;
    public $name;
    public $quantity;
    public $total_amount;
    public $currency = "MXN";
    public $type;
}