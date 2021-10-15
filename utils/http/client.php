<?php

require_once("response.php");
require_once("auth.php");
class Client 
{
    /**
     * These constant values ​​were taken from the official OpenControl documentation
     */
    const MAX_REDIRECTS = 10;
    const TIMEOUT = 30;

    public static function execute($url, $data = null, Auth $auth = null, $method = 'GET') {
        $curl = curl_init();

        $headers = [
            "Accept: application/json",
            "Content-Type: application/json"
        ];

        if($auth !== null){
            $headers[] = 'Authorization: Basic '.$auth->toBase64();
        }

        if($data !== null && $method === "POST"){
            $postfields = json_encode($data);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
        }

        curl_setopt_array($curl , [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => self::MAX_REDIRECTS,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = json_decode(curl_exec($curl), true);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $httpResponse = new Response();
        $httpResponse->httpCode = $httpCode;
        $httpResponse->body = $response;

        return $httpResponse;
    }
}