<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-08-03
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Robi {

    protected $CI;

    const USER_ID = "YOUR_ROBI_SMS_USER";
    const USER_PASSWORD = "YOUR_ROBI_SMS_PASSWORD";

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
        $curlError = NULL;
        $recipientMobileNumber = str_replace("+", "", $recipientMobileNumber);

        //https://api.mobireach.com.bd/SendTextMessage?Username=testuser&Password=XXXXXX&From=88018XXXXXXXX&To=8801XXXXXXXXX&Message=testmessage


        $baseURL = "https://api.mobireach.com.bd/SendTextMessage?";
        $getData = array(
            'Username' => Robi::USER_ID,
            'Password' => Robi::USER_PASSWORD,
            'From' => 'PARTNER_NAME',
            'To' => $recipientMobileNumber,
            'Message' => $message,
        );

        if ($senderMask) {
            if (in_array($senderMask, $this->supportedMasking)) {
                $getData['From'] = $senderMask;
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
        /*
          <?xml version="1.0" encoding="UTF-8"?>
          <ArrayOfServiceClass>
          <ServiceClass>
          <MessageId>0</MessageId>
          <Status>-1</Status>
          <StatusText>Error occurred</StatusText>
          <ErrorCode>1504</ErrorCode>
          <ErrorText>campaign_is_undefined</ErrorText>
          <SMSCount>1</SMSCount>
          <CurrentCredit>0</CurrentCredit>
          </ServiceClass>
          </ArrayOfServiceClass> */

        if (curl_error($c)) {
            $curlError = curl_error($c);
        }
        curl_close($c);


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
            if ($response['ServiceClass']['Status'] == 0) {
                return array(
                    'type' => 'success',
                    'code' => $response['ServiceClass']['StatusText'],
                    'messageid' => $response['ServiceClass']['MessageId'],
                    'message' => 'Successfully transmitted'
                );
            } else if ($response['ServiceClass']['Status'] == -1) {
                return array(
                    'type' => 'error',
                    'code' => $response['ServiceClass']['ErrorCode'],
                    'message' => $response['ServiceClass']['ErrorText']
                );
            } elseif ($response['ServiceClass']['Status'] == 1) {
                return array(
                    'type' => 'success',
                    'code' => $response['ServiceClass']['StatusText'],
                    'messageid' => $response['ServiceClass']['MessageId'],
                    'message' => 'Successfully transmitted'
                );
            } elseif ($response['ServiceClass']['Status'] == 2) {
                return array(
                    'type' => 'success',
                    'code' => $response['ServiceClass']['StatusText'],
                    'messageid' => $response['ServiceClass']['MessageId'],
                    'message' => 'Successfully transmitted'
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
