<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-08-09
 */

defined('BASEPATH') OR exit('No direct script access allowed');

/*
  Input parameters:
  username =abc {Valid User Name}[mandatory];
  password =xxxx { Valid password}[mandatory];
  apicode =1 {1= for Sending}[mandatory];
  msisdn =017XXXXXXXXX {Mobile Number Prefix with ZERO}[mandatory];
  countrycode =880{For Local Sms}[mandatory];
  cli =zzzyyyxxx{Valid CLI}[mandatory];
  messagetype=1 {1 for text ;2 for flash ;3 for unicode(bangla)}
  message =text {Text Scripts}[Mandatory];
  messageid=0 [mandatory];


  Success: -
  200,messageid (200:-Success, messageid :- Unique value)

  Failure: -
  201,IP BlackList
  202,Duplicate Session
  203,Invalid Username
  204,Invalid Password
  205,Invalid UserType
  206,Invalid Msisdn
  207,Invalid Country Code
  208:Invalid Cli
  209:Dnd User
  210,Parameter Mismatch
  216,Low Balance
  217, Number Barred
  220,Application Error
  221,Message Sending Fail
  223,Msisdn Must Be Start With Zero
  226,Invalid apicode

 */

class Grameenphone {

    protected $CI;

    const USER_ID = "YOUR_GP_SMS_USER";
    const USER_PASSWORD = "YOUR_GP_SMS_PASSWORD";

    function __construct() {
        $this->CI = & get_instance();
    }

    function sendSMS($dialingCode, $recipientMobileNumber, $message, $senderMask = NULL) {

        //$countryCode = substr($recipientMobileNumber, 0, 6);

        $prefix = substr($recipientMobileNumber, 0, 4);

        if (strpos($prefix, '+880') === FALSE || (strpos($prefix, '880') === FALSE)) {
            return array(
                'type' => 'error',
                'code' => 404,
                'message' => 'Invalid mobile number',
                'gateway_name' => 'Grameenphone'
            );
        } else {
            $recipientMobileNumber = str_replace("+", "", $recipientMobileNumber);
            $recipientMobileNumber = str_replace('880', "0", $recipientMobileNumber);
        }

//        $recipientMobileNumber = str_replace("+", "", $recipientMobileNumber);
//        $recipientMobileNumber = str_replace($dialingCode, "0", $recipientMobileNumber);
        if (substr($recipientMobileNumber, 0, 3) == '017') {

            $curlError = NULL;
            $baseURL = "https://cmp.grameenphone.com/gpcmpapi/messageplatform/controller.home?";

            $getData = array(
                'username' => Grameenphone::USER_ID,
                'password' => Grameenphone::USER_PASSWORD,
                'apicode' => 1,
                'cli' => 'PARTNER_NAME',
                'msisdn' => $recipientMobileNumber,
                'countrycode' => '880',
                'messagetype' => 1,
                'message' => $message,
                'messageid' => 0
            );

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
                if (strpos($response, ',') !== FALSE) {
                    $response = explode(',', $response);
                    if ($response[0] == 200) {
                        return array(
                            'type' => 'success',
                            'code' => 200,
                            'messageid' => $response[1],
                            'message' => 'Message sent successfully'
                        );
                    } else {
                        return array(
                            'type' => 'error',
                            'code' => $response[0],
                            'message' => $response[1]
                        );
                    }
                }
            } else {
                return array(
                    'type' => 'error',
                    'code' => 500,
                    'message' => 'No response from server'
                );
            }
        } else {
            return array(
                'type' => 'error',
                'code' => 404,
                'message' => 'Invalid mobile number',
                'gateway_name' => 'Grameenphone'
            );
        }
    }

}

?>