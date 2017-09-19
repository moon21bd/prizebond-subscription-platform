<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2016-11-11
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Surecash {

    public $_API_url;
    private $client_id;
    private $access_key;
    private $transactionId;
    private $partnerCode;
    private $mobileNumber;
    private $customerId;
    private $billNo;

    public function __construct($params = array()) {
        $CI = & get_instance();
        if ($params['SANDBOX']) {
            $this->_API_url = "https://sandbox.surecash.net/api/";
        } else {
            $this->_API_url = "https://api.surecash.net/api/";
        }

        // initilize the deafult params
        $this->access_key = (isset($params['access_key']) ? $params['access_key'] : '');
        $this->client_id = (isset($params['client_id']) ? $params['client_id'] : '');
        // $this->transactionId = $params['transactionId'];
        $this->partnerCode = $params['partner_code'];
        //$this->mobileNumber = $params['mobileNumber'];
        //$this->customerId = $params['customerId'];
        //$this->billNo = $params['billNo'];
    }

    //sure cash payment
    public function sureCashOnlinePayment($surecashAccountNo = 0, $customerMobileNumber = 0, $amount, $customerId, $billNo) {

        $url = $this->_API_url . 'payment';
        $requestBody = array(
            'partnerCode' => $this->partnerCode,
            'processId' => time(),
            'surecashAccountNo' => $surecashAccountNo,
            'customerMobileNumber' =>$customerMobileNumber,
            'amount' => $amount,
            'customerId' => $customerId,
            'billNo' => $billNo,
                //'feedBackUrl' => 'http://example.com',
        );
        $credentials = base64_encode("" . $this->client_id . ":" . $this->access_key); //Your company client id and access key
        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-type: application/json';
        $header[] = 'Authorization: basic ' . $credentials;

        $response = $this->curlPost($url, $credentials, $requestBody, $header);

        return json_decode($response);
    }

    // offline payment
    public function sureCashOfflinePayment($customerMobileNumber, $amount, $customerId, $billNo) {

        $url = $this->_API_url . 'offline/payment';

        $requestBody = array(
            'partnerCode' => $this->partnerCode,
            'customerMobileNumber' => $customerMobileNumber,
            'amount' => $amount,
            'customerId' => $customerId,
            'billNo' => $billNo
        );


//        echo '<pre>';
//        print_r($requestBody);
//        exit;

        $credentials = base64_encode($this->client_id . ":" . $this->access_key); //Your company client id and access key
        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-type: application/json';
        $header[] = 'Authorization: Basic ' . $credentials;

        $response = $this->curlPost($url, $credentials, $requestBody, $header);

        print_r($response);
        // return json_decode($response);
    }

    // online check status
    public function onlineCheckStatus($transactionId, $customerMobileNumber) {
        $url = $this->_API_url . 'payment/status';
        $credentials = base64_encode("" . $this->client_id . ":" . $this->access_key); //Your company client id and access key
        $requestBody = array(
            'transactionId' => $transactionId,
            'partnerCode' => $this->partnerCode,
            'mobileNumber' => $customerMobileNumber
        );
        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-type: application/json';
        $header[] = 'Authorization: basic ' . $credentials;

        $response = $this->curlGet($url, $requestBody, $header);
        $checkStatus = '';
        $result = json_decode($response);
        $status = $result->status;
        switch ($status) {
            case 200:
                $checkStatus = "status:SUCCESS.";
                break;
            case 200:
                $checkStatus = "status:PROCESSED.";
                break;
            case 9006:
                $checkStatus = "status:ERROR.";
                break;
            default:
                $checkStatus = "status:FAILED." . $status;
                break;
        }
        return status;
    }

    // offline status
    public function offileCheckStatus($invoiceNo) {
        $url = $this->_API_url . 'offline/payment/status/' . $invoiceNo;
        $credentials = base64_encode($this->client_id . ":" . $this->access_key);
        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-type: application/json';
        $header[] = 'Authorization: basic ' . $credentials;

        $response = $this->curlGet($url, '', $header);
        return json_decode($response);
    }

    //refund payment
    public function refundPayment($surecashAccountNo, $transactionId, $amount) {
        $url = $this->_API_url . 'refund';
        $requestBody = array(
            'partnerCode' => $this->partnerCode,
            'surecashAccountNo' => $surecashAccountNo,
            'transactionId' => $transactionId,
            'amount' => $amount
        );
        $credentials = base64_encode("" . $this->client_id . ":" . $this->access_key); //Your company client id and access key
        $header = array();
        $header[] = 'Accept: application/json';
        $header[] = 'Content-type: application/json';
        $header[] = 'Authorization: basic ' . $credentials;

        $response = $this->curlPost($url, $requestBody, $header);

        return json_decode($response);
    }

    private function curlGet($url, $requestBody = '', $header = '') {
        $ch = curl_init();
        if ($requestBody) {
            curl_setopt($ch, CURLOPT_URL, $url . "?" . http_build_query($requestBody));
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($header != '') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        $response = curl_exec($ch);

        if (curl_error($ch)) {
            echo 'error:' . curl_error($ch);
        }

        curl_close($ch);
        return $response;
    }

    //curl online
    private function curlPost($url, $credentials, $requestBody, $header = '') {
        echo $url;
        echo "<br>";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
        curl_setopt($ch, CURLOPT_USERPWD, $credentials);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($header != '') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        $response = curl_exec($ch);

        if (curl_error($ch)) {
            echo 'error:' . curl_error($ch);
        }

        curl_close($ch);

        echo '<pre>';
        print_r($response);
        echo '</pre>';
        exit;



        return $response;
    }

}
