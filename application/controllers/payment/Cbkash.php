<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'controllers/Main.php';

class Cbkash extends Main {

    public function __construct() {
        parent::__construct();
        //$this->load->library('session');
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
        
        $this->load->model('api_model');
        $this->load->model('global_model');
        $this->config->load('credentials');
        $params = array('msisdn' => $this->config->item('bmsisdn'), 'user' => $this->config->item('buser'), 'pass' => $this->config->item('bpass'));
        $this->load->library('bkash', $params);
        // $bKashResponse = $this->bkash->checkTranscationStatus(/* TrxId */'4618405025', /* amount */ '3995');
    }

    public function getBkashForm($productId, $price) {

//        echo '<pre>';
//        $bKashResponse = $this->bkash->checkTranscationStatus(/* TrxId */'4618405025', /* amount */ '3995');
//        print_r($bKashResponse);
//        exit;

        $sessionData = array(
            'payment_method_type_id' => 2,
            'product_id' => $productId,
            'price' => $price
        );
        $this->session->set_userdata($sessionData);

        if ($this->saveSubscriptionInformation()) {

            $data = array();
            if ($this->input->post('submit')) {

                $this->form_validation
                        ->set_rules('mobile', 'mobile', 'trim|required|numeric|min_length[10]|max_length[11]')
                        ->set_rules('transaction_id', 'transaction id', 'trim|required|numeric');

                if ($this->form_validation->run()) {

                    $reference_no = $this->input->post('reference_no');
                    $counter_no = $this->input->post('counter_no');
                    $mobile = $this->input->post('mobile');
                    $transactionId = $this->input->post('transaction_id');

                    if (!$this->api_model->checkExistingTrxID($transactionId)) {
                        $this->session->set_flashdata('error', 'This transaction id already used.');
                        //$data['error'] = 'This transaction id already used.';
                    } else {

                        $bKashResponse = $this->bkash->checkTranscationStatus(/* TrxId */$transactionId, /* amount */ $this->session->userdata('price'));

                        //if ($bKashResponse['status'] == 'success') {
                        if ($bKashResponse) {

                            $this->updateSubscriptionInformation();

                            $productInfo = $this->api_model->get_data('product_list', array('id' => $this->session->userdata('product_id')));

                            $productAttribueValue = $this->api_model->getProductAttributeAndValue($this->session->userdata('product_id'));

                            if (isset($productAttribueValue['unit']) && isset($productAttribueValue['validity(days)'])) {

                                if (!empty($productAttribueValue['unit']) && $productAttribueValue['unit'] == 'bond') {

                                    $validity = $productAttribueValue['validity(days)'];
                                    $validEndDateTime = date('Y-m-d H:i:s', strtotime("+ $validity days"));
                                }
                            }


                            if (isset($productAttribueValue['unit']) && !isset($productAttribueValue['validity(days)'])) {

                                if ($productAttribueValue['unit'] == 'bond') {
                                    $validEndDateTime = date('Y-m-d H:i:s', strtotime('+21 years'));
                                }
                            }

                            if (isset($productAttribueValue['unit']) && !isset($productAttribueValue['validity(days)'])) {

                                if ($productAttribueValue['unit'] == 'days') {

                                    $validity = $productAttribueValue['unit_value'];
                                    $validEndDateTime = date('Y-m-d H:i:s', strtotime("+ $validity days"));
                                }
                            }

                            if (!isset($productAttribueValue['unit']) && !isset($productAttribueValue['validity(days)'])) {
                                $validEndDateTime = date('Y-m-d H:i:s', strtotime('+21 years'));
                            }

                            $productPurchaseListData = array(
                                'user_id' => $this->session->userdata('userId'),
                                'product_category_id' => $productInfo['product_category_id'],
                                'product_type_id' => $productInfo['product_type_id'],
                                'app_types_id' => $productInfo['app_types_id'],
                                'product_list_id' => $productInfo['id'],
                                'purchased_by' => 'online paymemt',
                                'valid_start_datetime' => date('Y-m-d H:i:s'),
                                'valid_end_datetime' => $validEndDateTime,
                                'status' => 1,
                                'created_datetime' => date('Y-m-d H:i:s')
                            );

                            $this->global_model->insert('product_purchase_list', $productPurchaseListData);

                            $bKashInfo = array(
                                'trxId' => $transactionId,
                                'customer_mobile_number' => '+88' . $mobile,
                                'reference_no' => $reference_no,
                                'counter_no' => $counter_no,
                                'payment_status' => 'success',
                                'last_tried_date_time' => date('Y-m-d H:i:s')
                            );

                            $insertId = $this->session->userdata('last_insert_id');
                            $this->global_model->update('payment_bkash_queue', $bKashInfo, array('id' => $insertId));

                            //$this->session->sess_destroy();
                            //$this->api_model->save('payment_bkash_queue', $bKashInfo);
                            $this->session->set_flashdata('success', $bKashResponse['message']);
                            $data['success'] = $bKashResponse['message'];
                            redirect('payment/cbkash/showSuccessForm');
                        } elseif ($bKashResponse['status'] == 'failed') {
                            $this->session->set_flashdata('error', $bKashResponse['message']);
                            $data['error'] = $bKashResponse['message'];

                            $bKashInfo = array(
                                'trxId' => $transactionId,
                                'customer_mobile_number' => '+88' . $mobile,
                                'reference_no' => $reference_no,
                                'counter_no' => $counter_no,
                                'payment_status' => 'failed',
                                'last_tried_date_time' => date('Y-m-d H:i:s')
                            );

                            $insertId = $this->session->userdata('last_insert_id');
                            $this->global_model->update('payment_bkash_queue', $bKashInfo, array('id' => $insertId));
                        } else {
                            $this->session->set_flashdata('error', $bKashResponse['message']);
                            $data['error'] = $bKashResponse['message'];
                            $bKashInfo = array(
                                'trxId' => $transactionId,
                                'customer_mobile_number' => '+88' . $mobile,
                                'reference_no' => $reference_no,
                                'counter_no' => $counter_no,
                                'payment_status' => 'failed',
                                'last_tried_date_time' => date('Y-m-d H:i:s')
                            );
                            $insertId = $this->session->userdata('last_insert_id');
                            $this->global_model->update('payment_bkash_queue', $bKashInfo, array('id' => $insertId));
                        }
                    }
                } else {
                    $this->session->set_flashdata('error', str_replace("\n", ' ', validation_errors()));
                }
            }
            $this->load->view('subscriptions/bkash_form', $data);
        } else {
            $this->session->set_flashdata('error', 'Failed to save');
            //$this->load->view('subscriptions/bkash_form', $data);
        }
    }

    public function getBkashFormForAgent($productId, $price) {

        $sessionData = array(
            'payment_method_type_id' => 2,
            'product_id' => $productId,
            'price' => $price
        );
        $this->session->set_userdata($sessionData);

        if ($this->saveSubscriptionInformation()) {

            $data = array();
            if ($this->input->post('submit')) {

                $this->form_validation
                        ->set_rules('mobile', 'mobile', 'trim|required|numeric|min_length[10]|max_length[11]')
                        ->set_rules('transaction_id', 'transaction id', 'trim|required|numeric');

                if ($this->form_validation->run()) {

                    $reference_no = $this->input->post('reference_no');
                    //$counter_no = $this->input->post('counter_no');
                    $mobile = $this->input->post('mobile');
                    $transactionId = $this->input->post('transaction_id');

                    if (!$this->api_model->checkExistingTrxID($transactionId)) {
                        $this->session->set_flashdata('error', 'This transaction id already used.');
                        //$data['error'] = 'This transaction id already used.';
                    } else {

                        //$bKashResponse = $this->bkash->checkTranscationStatus(/* TrxId */$transactionId, /* amount */ $this->session->userdata('price'));
                        //if ($bKashResponse['status'] == 'success') {

                        $this->updateSubscriptionInformation();

                        $productInfo = $this->api_model->get_data('product_list', array('id' => $this->session->userdata('product_id')));

                        $productAttribueValue = $this->api_model->getProductAttributeAndValue($this->session->userdata('product_id'));

                        if (isset($productAttribueValue['unit']) && isset($productAttribueValue['validity(days)'])) {

                            if (!empty($productAttribueValue['unit']) && $productAttribueValue['unit'] == 'bond') {

                                $validity = $productAttribueValue['validity(days)'];
                                $validEndDateTime = date('Y-m-d H:i:s', strtotime("+ $validity days"));
                            }
                        }


                        if (isset($productAttribueValue['unit']) && !isset($productAttribueValue['validity(days)'])) {

                            if ($productAttribueValue['unit'] == 'bond') {
                                $validEndDateTime = date('Y-m-d H:i:s', strtotime('+21 years'));
                            }
                        }


                        if (isset($productAttribueValue['unit']) && !isset($productAttribueValue['validity(days)'])) {

                            if ($productAttribueValue['unit'] == 'days') {

                                $validity = $productAttribueValue['unit_value'];
                                $validEndDateTime = date('Y-m-d H:i:s', strtotime("+ $validity days"));
                            }
                        }

                        if (!isset($productAttribueValue['unit']) && !isset($productAttribueValue['validity(days)'])) {
                            $validEndDateTime = date('Y-m-d H:i:s', strtotime('+21 years'));
                        }

                        $productPurchaseListData = array(
                            'user_id' => $this->session->userdata('userId'),
                            'product_category_id' => $productInfo['product_category_id'],
                            'product_type_id' => $productInfo['product_type_id'],
                            'app_types_id' => $productInfo['app_types_id'],
                            'product_list_id' => $productInfo['id'],
                            'purchased_by' => 'online paymemt',
                            'valid_start_datetime' => date('Y-m-d H:i:s'),
                            'valid_end_datetime' => $validEndDateTime,
                            'status' => 1,
                            'created_datetime' => date('Y-m-d H:i:s')
                        );

                        $this->global_model->insert('product_purchase_list', $productPurchaseListData);

                        $bKashInfo = array(
                            'trxId' => $transactionId,
                            'customer_mobile_number' => '+88' . $mobile,
                            'reference_no' => $reference_no,
                            'counter_no' => 0,
                            'payment_status' => 'success',
                            'last_tried_date_time' => date('Y-m-d H:i:s')
                        );

                        $insertId = $this->session->userdata('last_insert_id');
                        $this->global_model->update('payment_bkash_queue', $bKashInfo, array('id' => $insertId));

                        //$this->session->sess_destroy();
                        //$this->api_model->save('payment_bkash_queue', $bKashInfo);
                        $this->session->set_flashdata('success', 'Transaction was successfull');
                        $data['success'] = 'Transaction was successfull';
                        redirect('payment/cbkash/showSuccessForm');
                        //}

                        /* elseif ($bKashResponse['status'] == 'failed') {
                          $this->session->set_flashdata('error', $bKashResponse['message']);
                          $data['error'] = $bKashResponse['message'];

                          $bKashInfo = array(
                          'trxId' => $transactionId,
                          'customer_mobile_number' => '+88' . $mobile,
                          'reference_no' => $reference_no,
                          'counter_no' => 0,
                          'payment_status' => 'failed',
                          'last_tried_date_time' => date('Y-m-d H:i:s')
                          );

                          $insertId = $this->session->userdata('last_insert_id');
                          $this->api_model->updateInfo('payment_bkash_queue', $bKashInfo, array('id' => $insertId));
                          }
                          else {
                          $this->session->set_flashdata('error', $bKashResponse['message']);
                          $data['error'] = $bKashResponse['message'];
                          $bKashInfo = array(
                          'trxId' => $transactionId,
                          'customer_mobile_number' => '+88' . $mobile,
                          'reference_no' => $reference_no,
                          'counter_no' => 0,
                          'payment_status' => 'failed',
                          'last_tried_date_time' => date('Y-m-d H:i:s')
                          );
                          $insertId = $this->session->userdata('last_insert_id');
                          $this->api_model->updateInfo('payment_bkash_queue', $bKashInfo, array('id' => $insertId));
                          }
                         * 
                         */
                    }
                } else {
                    $this->session->set_flashdata('error', str_replace("\n", ' ', validation_errors()));
                }
            }
            $this->load->view('subscriptions/bkash_form_send_money', $data);
        } else {
            $this->session->set_flashdata('error', 'Failed to save');
            //$this->load->view('subscriptions/bkash_form', $data);
        }
    }

    public function showSuccessForm() {
        $this->load->view('subscriptions/bkash_success_form');
    }

//(
//    [status] => success
//    [message] => transaction was successfull
//    [data] => stdClass Object
//        (
//            [amount] => 3995
//            [counter] => 1
//            [currency] => BDT
//            [receiver] => 01700000000
//            [reference] => 5
//            [sender] => 01700000001
//            [service] => Payment
//            [trxId] => 4618405025
//            [trxStatus] => 0000
//            [trxTimestamp] => 2016-04-20T15:14:59.677+06:00
//        )
//
//)
}
