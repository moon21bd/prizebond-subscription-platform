<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-24
 */

defined('BASEPATH') OR exit('No direct script access allowed');

/*
STATUS

ALL_RECIPIENTS_PROCESSED
SEND_ERROR
NOT_ENOUGH_CREDITS
NETWORK_NOTCOVERED
INVALID_USER_OR_PASS
MISSING_DESTINATION_ADDRESS
MISSING_USERNAME
MISSING_PASSWORD
INVALID_DESTINATION_ADDRESS
SYNTAX_ERROR
ERROR_PROCESSING
COMMUNICATION_ERROR
INVALID_DELIVERY_REPORT_PUSH_URL
DUPLICATE_MESSAGEID
SENDER_NOT_ALLOWED
GENERAL_ERROR

VALUE DESCRIPTION
0 Request was successful (all recipients)
-1 Error in processing the request
-2 Not enough credits on a specific account
-3 Targeted network is not covered on specific account
-5 Username or password is invalid
-6 Destination address is missing in the request
-10 Username is missing in the request
-11 Password is missing in the request
-13 Number is not recognized by the platform
-22 Incorrect format, caused by syntax error
-23 General error, reasons may vary
-26 General API error, reasons may vary
-28 Invalid PushURL in the request
-33 Duplicated MessageID in the request
-34 Sender name is not allowed
-99 Error in processing request, reasons may vary
*/


class Muthofun {

    protected $CI;

    function __construct() {
        $this->_ci = & get_instance();
    }
    
    
    function sendSMS($recipient, $message){	
        $userName = getenv('MUTHOFUN_USER') ?: 'YOUR_MUTHOFUN_USER';
        $password = getenv('MUTHOFUN_PASSWORD') ?: 'YOUR_MUTHOFUN_PASSWORD';
        
        $data = array(
            'sms' => $message
        );

        $url = "http://clients.muthofun.com:8901/esmsgw/sendsms.jsp?user=$userName&password=$password&mobiles=$recipient&".http_build_query($data).'&unicode=1';
        
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 3);
        $response = curl_exec($c);
        curl_close($c);
        
        if($response){
            
            $xml = simplexml_load_string($response);
            $resultArray = json_decode(json_encode($xml), TRUE);
            
            if(json_last_error() === JSON_ERROR_NONE){
                
                if(is_array($resultArray) && (!empty($resultArray['sms']))){
                    
                    if(strlen($resultArray['sms']['messageid']) > 5){
                        return array(
                            'type' => 'success',
                            'code' => 4000,
                            'message' => 'message sent successfully'
                        );
                    }
                }
                else if(is_array($resultArray) && (!empty($resultArray['error']))){
                    return array(
                        'type' => 'error',
                        'code' => $resultArray['error']['error-code'],
                        'message' => $resultArray['error']['error-description']
                    );
                }
            }
            else {
                return array(
                    'type' => 'error',
                    'code' => 4001,
                    'message' => 'invalid xml data format'
                );
            }
            
        }
        else {
            return array(
                'type' => 'error',
                'code' => 500,
                'message' => 'No response from server'
            );
        }
        
    }
    
    
    
    public function sendSingleSMS($senderId,$message,$recipient){
        
        /*
         * payload example
        {
            "authentication": {
                                "username": "test",
                                "password": "test"
                              },
            "messages": [
                            {
                                "sender": "044XXXXXXXX",
                                "text": "Hello",
                                "recipients": [
                                                {
                                                    "gsm": "88017XXXXXXXX"
                                                }
                                              ]
                            }
                        ]
        }*/
        // user_api
        // (credential removed)

        $recipients[] = array('gsm' => $recipient);
        $authentication = array('username' => getenv('MUTHOFUN_USER') ?: 'YOUR_MUTHOFUN_USER', 'password' => getenv('MUTHOFUN_PASSWORD') ?: 'YOUR_MUTHOFUN_PASSWORD');
        $message = array('sender' => $senderId, 'message' => $message, 'recipients' => $recipients);
        $messages[] = $message;
        $payload = array('authentication' => $authentication, 'messages' => $messages);
        $payloadJSON = json_encode($payload);
        
        echo "payload data : ".$payloadJSON;
        echo "<br>";

        $accessPoint = 'http://clients.muthofun.net/api/v3/sendsms/json';
        $ch = curl_init($accessPoint);                                                                   
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJSON);                                                                
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',
            'Accept: */*'                                                  
        ));                                                                                                                 

        $result = curl_exec($ch);
        
        if($result){
            
            
            $resultArray = json_decode($result,TRUE);
            
            if(json_last_error() === JSON_ERROR_NONE){
                
                if(is_array($resultArray)){
                    foreach ($resultArray['results'] as $result) {
                        
                        if($result['status'] == 0){
                            return array(
                                'type' => 'success',
                                'code' => 4000,
                                'message' => 'message sent successfully'
                            );
                        }
                        else {
                            return array(
                                'type' => 'error',
                                'code' => $result['status'],
                                'message' => getMessageByStatusCode($result['status'])
                            );
                        }
                        
                        
                    }
                }
                
                
                
            }
            else {
                return array(
                    'type' => 'error',
                    'code' => 4001,
                    'message' => 'invalid json data format'
                );
            }
            
            
            
            
        }
        else {
            return array(
                'type' => 'error',
                'code' => 500,
                'message' => 'No response from server'
            );
        }
        
    }
    
    private function getMessageByStatusCode($status){
     
        
        switch ($status) {
            case 0:
                return 'Request was successful (all recipients)';
                break;
            case -1:
                return 'Error in processing the request';
                break;
            case -2:
                return 'Not enough credits on a specific account';
                break;
            case -3:
                return 'Targeted network is not covered on specific account';
                break;
            case -5:
                return 'Username or password is invalid';
                break;
            case -6:
                return 'Destination address is missing in the request';
                break;
            case -10:
                return 'Username is missing in the request';
                break;
            case -11:
                return 'Password is missing in the request';
                break;
            case -13:
                return 'Number is not recognized by the platform';
                break;
            case -22:
                return 'Incorrect format, caused by syntax error';
                break;
            case -23:
                return 'General error, reasons may vary';
                break;
            case -26:
                return 'General API error, reasons may vary';
                break;
            case -28:
                return 'Invalid PushURL in the request';
                break;
            case -33:
                return 'Duplicated MessageID in the request';
                break;
            case -34:
                return 'Sender name is not allowed';
                break;
            case -99:
                return 'Error in processing request, reasons may vary';
                break;
            
            default:
                break;
        }
    }
    
    
    
}

?>