<?php

class ValidationService {
    public static function create($order, $extraInformation){
        $request = new Validation();
        self::setGeneralInformation($order, $request, $extraInformation);
        self::setProductsInformation($order, $request);
        self::setCustomerInformation($order, $request);
        self::setPaymentInformation($order, $request, $extraInformation);
        self::setShippingInformation($order, $request);
        self::setBillingInformation($order, $request);
        self::setMerchantInformation($order, $request);   
        return $request;
    }

    public static function setGeneralInformation($order, $request, $extraInformation) {
        $request->id = IdGenerator::generate(10);
        $request->session_id = $extraInformation->session;
        $request->amount = $order->get_total();
        $request->order_id = $order->get_id();
    }

    public static function setProductsInformation($order, $request) {
        $items = $order->get_items();
        foreach ($items as $item) {
            $product = new Product();
            $product->id = $item->get_product_id();
            $product->name = $item->get_name();
            $product->quantity = $item->get_quantity();
            $product->total_amount = $product->quantity * $item->get_product()->get_price();
            $product->type = ($item->get_product()->is_downloadable()) ? Product::TYPE_OF_PRODUCTS["DIGITAL"] : Product::TYPE_OF_PRODUCTS["PHYSICAL"];
            $request->products[] = $product;
        }
    }
    
    public static function setCustomerInformation($order, $request) {
        $request->customer->id = ($order->get_customer_id() == 0 ) ? 'GUEST' : $order->get_customer_id();
        $request->customer->name = ($order->get_billing_first_name()) ? $order->get_billing_first_name() : $order->get_shipping_first_name();
        $request->customer->last_name = ($order->get_billing_last_name()) ? $order->get_billing_last_name() : $order->get_shipping_last_name() ;
        $request->customer->phone = ($order->get_billing_phone()) ? $order->get_billing_phone() : $order->get_shipping_phone();
        $request->customer->email = ($order->get_billing_email()) ? $order->get_billing_email() : $order->get_shipping_email();
    }
    
    public static function setPaymentInformation($order, $request, $extraInformation) {
        $request->payment->address->line1 = ($order->get_billing_address_1()) ? $order->get_billing_address_1() : $order->get_shipping_address_1();
        $request->payment->address->line2 = ($order->get_billing_address_2()) ? $order->get_billing_address_2() : $order->get_shipping_address_2();
        $request->payment->address->city = ($order->get_billing_city()) ? $order->get_billing_city() : $order->get_shipping_city();
        $request->payment->address->state = ($order->get_billing_state()) ? $order->get_billing_state() : $order->get_shipping_state();
        $request->payment->address->postal_code = ($order->get_billing_postcode()) ? $order->get_billing_postcode() : $order->get_shipping_postcode();
        $request->payment->card->number = $extraInformation->number;
        $request->payment->card->holder_name = $extraInformation->holder;
    }

    public static function setShippingInformation($order, $request) {
        $request->shipping->line1 = ($order->get_billing_address_1()) ? $order->get_billing_address_1() : $order->get_shipping_address_1();
        $request->shipping->line2 = ($order->get_billing_address_2()) ? $order->get_billing_address_2() : $order->get_shipping_address_2();
        $request->shipping->city = ($order->get_billing_city()) ? $order->get_billing_city() : $order->get_shipping_city();
        $request->shipping->state = ($order->get_billing_state()) ? $order->get_billing_state() : $order->get_shipping_state();
        $request->shipping->postal_code = ($order->get_billing_postcode()) ? $order->get_billing_postcode() : $order->get_shipping_postcode();
    }

    public static function setBillingInformation($order, $request) {
        $request->shipping->line1 = ($order->get_shipping_address_1()) ? $order->get_shipping_address_1() : $order->get_billing_address_1();
        $request->shipping->line2 = ($order->get_shipping_address_2()) ? $order->get_shipping_address_2() : $order->get_billing_address_2();
        $request->shipping->city = ($order->get_shipping_city()) ? $order->get_shipping_city() : $order->get_billing_city();
        $request->shipping->state = ($order->get_shipping_state()) ? $order->get_shipping_state() : $order->get_billing_state();
        $request->shipping->postal_code = ($order->get_shipping_postcode()) ? $order->get_shipping_postcode() : $order->get_billing_postcode();
    }

    public static function setMerchantInformation($order, $request) {
        $enviroment = (isset(get_option(Constants::SETTINGS_NAME)['is_sandbox'])) ? 'sandbox' : 'live';
        $merchantId = get_option(Constants::SETTINGS_NAME)[$enviroment.'_merchant_id'];
        $request->merchant->id = $merchantId;
    }
}
