<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-19
 */

defined('BASEPATH') OR exit('No direct script access allowed');
/*
  Please find the below OUTGONIG ESMS API:
  API: https://vas.banglalinkgsm.com/sendSMS/sendSMS?msisdn=<msisdn>&message=<message>&userID=<UserID>&passwd=<password>&sender=<sender>
  Before https request please set all 4 parameters: <msisdn> format(for local:88019xxxxxxxx;88017xxxxxxxx etc,for international:+0088019xxxxxxxx;+0088017xxxxxxxx etc), <message>,<UserID>and <password>.
  And <sender> parameter is masking name which is optional.
  ESMS API response code:
  Please find the ESMS API guide as below:
  response format: plain text
  and responses value may be as below:
  ->Bill MSISDN is disconnected...!
  ->Sorry you send wrong password
  ->Sorry you send wrong username
  ->More then  5 sms not allowed
  ->Success Count :  and Fail Count :
  <UserID>and <password> will share in separate mail.
 */

class Banglalink {

    protected $CI;

    const USER_ID = "YOUR_BANGLALINK_SMS_USER";
    const USER_PASSWORD = "YOUR_BANGLALINK_SMS_PASSWORD";

    private $supportedMasking = array();

    function __construct() {
        $this->CI = & get_instance();

        $this->supportedMasking = array(
            'GOLPOKOBITA',
            'PRIZE BOND',
            'PARTNER_NAME',
            'TOP BDNEWS'
        );
    }

    function sendSMS($dialingCode, $recipientMobileNumber, $message, $senderMask = NULL) {
        $recipientMobileNumber = str_replace("+", "", $recipientMobileNumber);
        $curlError = NULL;
        $baseURL = "https://vas.banglalinkgsm.com/sendSMS/sendSMS?";
        $getData = array(
            'msisdn' => $recipientMobileNumber,
            'userID' => Banglalink::USER_ID,
            'passwd' => Banglalink::USER_PASSWORD,
            'message' => $message,
            'sender' => 'PARTNER_NAME'
        );

        if ($senderMask) {
            if (in_array($senderMask, $this->supportedMasking)) {
                $getData['sender'] = $senderMask;
            }
        }

        $url = $baseURL . '' . http_build_query($getData);

        $c = curl_init();
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 4); // The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
        curl_setopt($c, CURLOPT_TIMEOUT, 4); // The maximum number of seconds to allow cURL functions to execute.
        $response = curl_exec($c);

        if (curl_error($c)) {
            $curlError = curl_error($c);
        }
        curl_close($c);

        if ($curlError) {
            return array(
                'type' => 'error',
                'code' => -1,
                'message' => $curlError
            );
        } else if ($response) {

            if (strpos($response, 'and')) {
                //Success Count : 1 and Fail Count : 0
                $arr = explode('and', $response);
                $arr2 = explode(':', $arr[0]);

                if (trim($arr2[1]) > 0) {
                    return array(
                        'type' => 'success',
                        'code' => 200,
                        'message' => 'Message sent successfully'
                    );
                } else {
                    return array(
                        'type' => 'error',
                        'code' => 0,
                        'message' => 'Failed to sent message'
                    );
                }
            } else {
                return array(
                    'type' => 'error',
                    'code' => 500,
                    'message' => 'unhandle response from server. response : ', $response
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

}
