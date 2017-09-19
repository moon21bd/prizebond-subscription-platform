<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-11
 */

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Experttexting {

    protected $CI;

    function __construct() {
        $this->_ci = & get_instance();
    }

    // Base URLS for three methods
    public $base_url_SendSMS = 'https://www.experttexting.com/exptapi/exptsms.asmx/SendSMS';
    public $base_url_SendSMSUnicode = 'https://www.experttexting.com/exptapi/exptsms.asmx/SendSMSUnicode';
    public $base_url_QueryBalance = 'https://www.experttexting.com/exptapi/exptsms.asmx/QueryBalance';
    // Public Variables that are used as parameters in API calls
    public $username = 'YOUR_EXPERTTEXTING_USER';
    public $password = 'YOUR_EXPERTTEXTING_PASSWORD';
    public $apikey = 'YOUR_EXPERTTEXTING_API_KEY';
    public $msgtext = '';  // LET THIS REMAIN BLANK
    public $from = 'PARTNER_NAME';  // LET THIS REMAIN BLANK
    public $to = '';  // LET THIS REMAIN BLANK

    // SEND SMS FUNCTION FOR SIMPLE TEXT

    public function sendSMS($sender, $message, $mobileNumber) {
        $this->from = $sender;
        $curlError = NULL;
        $fieldcnt = 6;

//        $fieldstring = "Userid=$this->username&pwd=$this->password&APIKEY=$this->apikey&MSG=$this->msgtext&FROM=$this->from&To=$this->to";
        $fieldstring = "Userid=$this->username&pwd=$this->password&APIKEY=$this->apikey&MSG=$message&FROM=$this->from&To=$mobileNumber";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url_SendSMS);
        curl_setopt($ch, CURLOPT_POST, $fieldcnt);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldstring);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);




        /* <?xml version="1.0" encoding="utf-8"?>
          <ExpertTextAPI>
          <Status>SUCCESS</Status>
          <Country>880</Country>
          <TO>8801XXXXXXXXX</TO>
          <MsgId>124004588</MsgId>
          </ExpertTextAPI> */

        /*
          <?xml version="1.0" encoding="utf-8"?>
          <ExpertTextAPI>
          <Status>Unrecoginzed or Invalid Number!</Status>
          </ExpertTextAPI>
         */


        if (curl_error($ch)) {
            $curlError = curl_error($ch);
        }
        curl_close($ch);


        if (!empty($response)) {

            $xmlError = '';
            libxml_use_internal_errors(true);
            $sxe = simplexml_load_string($response);
            if ($sxe === false) {
                $xmlError .= "Failed loading XML\n";
                foreach (libxml_get_errors() as $error) {
                    $xmlError .= "\t" . $error->message;
                }

                return array(
                    'type' => 'error',
                    'code' => -2,
                    'message' => $xmlError
                );
            } else {
                $response = json_decode(json_encode($sxe), 1);
            }
        }

        if ($curlError) {
            return array(
                'type' => 'error',
                'code' => -1,
                'message' => $curlError
            );
        } elseif ($response) {
            if ($response['Status'] == 'SUCCESS') {
                return array(
                    'type' => 'success',
                    'code' => 200,
                    'messageid' => $response['MsgId'],
                    'message' => 'Successfully transmitted'
                );
            } else {
                return array(
                    'type' => 'error',
                    'code' => 502,
                    'message' => $response['Status']
                );
            }
        } else {
            return array(
                'type' => 'error',
                'code' => 500,
                'message' => 'No response from server'
            );
        }
    }

    // SEND SMS FUNCTION FOR UNICODE TEXT
    public function sendUnicode() {
        $fieldcnt = 6;
        $fieldstring = "Userid=$this->username&pwd=$this->password&APIKEY=$this->apikey&MSG=$this->msgtext&FROM=$this->from&To=$this->to";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url_SendSMSUnicode);
        curl_setopt($ch, CURLOPT_POST, $fieldcnt);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldstring);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    // FUNCTION TO QUERY YOUR ACCOUNT BALANCE
    public function QueryBalance() {
        $fieldcnt = 3;
        $fieldstring = "Userid=$this->username&pwd=$this->password&APIKEY=$this->apikey";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url_QueryBalance);
        curl_setopt($ch, CURLOPT_POST, $fieldcnt);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldstring);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

}
