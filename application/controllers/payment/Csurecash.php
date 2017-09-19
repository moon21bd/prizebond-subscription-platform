<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'controllers/Main.php';

class Csurecash extends Main {

    public function __construct() {
        parent::__construct();
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
        
        $this->config->load('credentials');
        $params = array('access_key' => $this->config->item('access_key'), 'client_id' => $this->config->item('client_id'), 'partner_code' => $this->config->item('partner_code'), 'SANDBOX' => FALSE);
        $this->load->library('surecash', $params);
    }

    public function getSureCashForm() {
        //sandbox response
        ////9003 Account Missing : There is no Account available to charge.
        //https://api.surecash.net/api/payment{"partnerCode":"JBL","partnerAccountNo":null,"note2":null,"note1":null,"customerMobileNumber":null,"status":"FAILED","transactionId":"N/A","statusCode":"9003","description":"Partner account not found.","invoiceNumber":null,"amount":0.0,"customerId":null}
        //{"customerMobileNumber":null,"transactionId":null,"partnerAccountNumber":null,"partnerCode":null,"billNo":null,"customerId":null,"smsTemplate":null,"note":null,"description":"No Biller Information Found for JBL","amount":null,"status":"FAILED","statusCode":"N/A","invoiceNo":null}
        //error:SSL: certificate subject name 'api.surecash.net' does not match target host name 'sandbox.surecash.net'
        //{"status": "UNAUTHORIZED", "description": "the merchant does not have access to this service."}
        //{"status":"FAILED","statusCode":"9000","description":"Process id is missing in request"}

        $this->surecash->sureCashOnlinePayment('+8801700000002', '+8801700000003', '60', '12345', '24234');
 
        //$this->surecash->sureCashOfflinePayment('01700000004', '50', '14596', '25874');

        exit;

        if ($this->saveSubscriptionInformation()) {
            $data = array();

            $this->load->view('subscriptions/surecash_form', $data);
        } else {
            echo 'Fail to save data.';
        }
    }

}
