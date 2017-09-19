<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-08-22
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'controllers/Main.php';

class Orders extends Main {

    public function __construct() {
        parent::__construct();
        if ($this->checkHost()) {
            $this->checkAuth();
        }

        if ($this->input->get('debug') == 1) {
            $this->output->enable_profiler(TRUE);
        }

        $this->load->model('subscription/orders_model');
        $this->load->model('subscription/product_model');
        $this->load->model('subscription/coupon_model');
        $this->load->model('admin_model');
        $this->load->model('global_model');
        $this->load->model('subscription/productpurchase_model');
        $this->load->helper('user_profile_info_helper');
        $this->load->helper('global');
    }

    public function __destruct() {
        $this->db->close();
    }

    public function index() {
        redirect('subscription/orders/history');
    }

    public function add() {
        $data = array();
        $data['title'] = 'Add';
        $data['tab_active'] = 'orderlist';


        $data['getProductList'] = $this->orders_model->getProductName();
        $data['getPaymentList'] = $this->orders_model->getPaymentMethod();
        if ($this->input->post('submit')) {
            $this->form_validation
                    ->set_rules('product_id', 'Product Name', 'trim|required')
                    ->set_rules('user_id', 'User Id', 'trim|required')
                    ->set_rules('price', 'Price', 'trim|required')
                    ->set_rules('payment_method_type_id', 'Payment Method ', 'trim|required');
            if ($this->form_validation->run()) {
                $id = $this->db->insert_id();
                $save_data = array();


                $save_data['product_id'] = $this->input->post('product_id');
                $save_data['user_id'] = $this->input->post('user_id');
                $save_data['price'] = $this->input->post('price');
                $save_data['payment_method_type_id'] = $this->input->post('payment_method_type_id');
                $save_data['created_date_time'] = date('Y-m-d H:i:s');
                $save_data['order_created_date_time'] = date('Y-m-d H:i:s');

                if ($insert_id = $this->orders_model->saveOrderList($save_data)) {
                    $this->session->set_flashdata('success_msg', 'Order List Saved Successfully...');
                    redirect('subscription/orders/manage');
                }
            }
        }

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/orders/add', $data);
        $this->load->view('admin/footer', $data);
    }

    public function history() {

        $data = array();
        $data['title'] = 'Order History';
        //$data['tab_active'] = 'orderlist';
        $data['paymentMethod'] = $this->orders_model->getPaymentMethod();
        $data['paymentSelect'] = '';

        $uri_segment = 5;
        $limit = 20;
        $offset = 0;
        if ($this->uri->segment(5) === FALSE) {
            $offset = 0;
        } else {
            $offset = $this->uri->segment(5) ? $this->uri->segment(5) : 0;
        }

        $userId = NULL;
        $searchQuery = NULL;
        $paymentName = NULL;
        $paymentStatus = NULL;
        $orderStatus = NULL;
        $product = NULL;

        $startDate = $this->session->userdata('start_date') ? $this->session->userdata('start_date') : date("Y-m-d", strtotime("- 60 days"));
        $endDate = $this->session->userdata('end_date') ? $this->session->userdata('end_date') : date("Y-m-d");


        $userId = (!empty($this->input->get("user_id"))) ? $this->input->get("user_id") : NULL;

        if ($this->input->get()) {
            $getData = array();
            foreach ($this->input->get() as $key => $value) {
                $getData[$key] = $value;
            }

            $this->form_validation->set_data($getData);

            if ($this->input->get('search') == 1) {
                $this->form_validation->set_rules('search_query', '', 'trim');
                $this->form_validation->set_rules('payment_name', '', 'trim');
                $this->form_validation->set_rules('payment_status', '', 'trim');
                $this->form_validation->set_rules('product', '', 'trim');
            }
            if ($this->form_validation->run() == TRUE) {
                $searchQuery = trim($this->input->get('search_query'));
                $paymentName = trim($this->input->get('payment_name'));
                $paymentStatus = trim($this->input->get('payment_status'));
                $orderStatus = trim($this->input->get('order_status'));
                $product = trim($this->input->get('product'));
            } else {
                echo validation_errors();
            }
        }

        $orderListInfo = $this->orders_model->getOrderList($userId, $limit, $offset, $startDate, $endDate, $searchQuery, $paymentName, $paymentStatus, $orderStatus, $product);

        if ($this->session->userdata('userRole') == Main::USER_ROLE_CRM) {
            if ($this->input->get()) {
                $data['orderList'] = $orderListInfo['result'];
                $total_rows = $orderListInfo['total'];
            } else {
                $data['orderList'] = '';
                $total_rows = 0;
            }
        } else {
            $data['orderList'] = $orderListInfo['result'];
            $total_rows = $orderListInfo['total'];
        }

        generatePagging('/subscription/orders/history/offset/', $total_rows, $limit, $uri_segment, 4);

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/orders/manage', $data);
        $this->load->view('subscription/footer', $data);
    }

    public function todaysOrder() {
        $data = array();
        $data['title'] = "Today's Order List";
        //$data['tab_active'] = 'orderlist';
        $data['paymentMethod'] = $this->orders_model->getPaymentMethod();
        $data['paymentSelect'] = '';

        $uri_segment = 5;
        $limit = 20;
        $offset = 0;
        if ($this->uri->segment(5) === FALSE) {
            $offset = 0;
        } else {
            $offset = $this->uri->segment(5) ? $this->uri->segment(5) : 0;
        }

        $userId = NULL;
        $searchQuery = NULL;
        $paymentName = NULL;
        $paymentStatus = NULL;
        $orderStatus = NULL;
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');

        if ($this->input->get()) {
            $getData = array();
            foreach ($this->input->get() as $key => $value) {
                $getData[$key] = $value;
            }

            $this->form_validation->set_data($getData);

            if ($this->input->get('search') == 1) {
                $this->form_validation->set_rules('search_query', '', 'trim');
                $this->form_validation->set_rules('payment_name', '', 'trim');
                $this->form_validation->set_rules('payment_status', '', 'trim');
            }
            if ($this->form_validation->run() == TRUE) {
                $searchQuery = trim($this->input->get('search_query'));
                $paymentName = trim($this->input->get('payment_name'));
                $paymentStatus = trim($this->input->get('payment_status'));
                $orderStatus = trim($this->input->get('order_status'));
            } else {
                echo validation_errors();
            }
        }

        $orderListInfo = $this->orders_model->getOrderList($userId, $limit, $offset, $startDate, $endDate, $searchQuery, $paymentName, $paymentStatus, $orderStatus);
        $data['orderList'] = $orderListInfo['result'];
        $total_rows = $orderListInfo['total'];

        if (!empty($userId)) {
            generatePagging('/subscription/orders/todaysOrder/offset/' . $userId, $total_rows, $limit, $uri_segment, 4);
        } else {
            generatePagging('/subscription/orders/todaysOrder/offset/', $total_rows, $limit, $uri_segment, 4);
        }

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        //$this->load->view('subscription/sidebar', $data);
        $this->load->view('subscription/orders/today_order', $data);
        $this->load->view('admin/footer', $data);
    }

    public function view($id) {
        $data = array();
        $data['title'] = ' View';
        $data['tab_active'] = 'orderlist';

        $orderInfo = $this->orders_model->getOrderInfoById($id);

        $data['users_personal_info'] = $this->admin_model->getAUserInfoById($orderInfo->user_id);
        $data['productInfo'] = $this->product_model->getProductInfoById($orderInfo->product_id);

        if ($orderInfo->payment_method_type_id == 7) {
            $data['CODInfo'] = $this->global_model->get_data('subscription_coupons', array('used_by_user_id' => $orderInfo->user_id, 'order_id' => $orderInfo->reference_id));
        } else {
            $data['CODInfo'] = '';
        }
        $data['orderListInfo'] = $orderInfo;

        $this->load->view('subscription/orders/view', $data);
    }

    public function userInfo($id) {
        $data = array();
        $data['title'] = 'Users';
        $data['profile'] = $this->orders_model->userDetails($id);

        $this->load->view('subscription/orders/profile', $data);
    }

    public function productView($id) {
        $data = array();
        $data['title'] = 'View Product';

        $data['productInfo'] = $this->product_model->getProductInfoById($id);

        $this->load->view('subscription/product/view', $data);
        ;
    }

    public function changeStatusForm($orderId, $status) {

        $data = array();
        $data['orderStatus'] = $status;
        $data['orderId'] = $orderId;

        $currentOrderInfo = $this->orders_model->get_data('subscription_order_list', array('id' => $orderId));

        $data['shippingAddress'] = $currentOrderInfo->shipping_address ? trim($currentOrderInfo->shipping_address) : '';

        $this->load->view('subscription/orders/change_status_form', $data);
    }

    public function changeCODOrderStatus_ajax() {

        if ($this->input->post()) {

            $this->form_validation->set_rules('status', '', 'trim|required');
            $this->form_validation->set_rules('order_id', '', 'trim|required');
            $this->form_validation->set_rules('note', '', 'trim');
            $this->form_validation->set_rules('delivery_id', '', 'trim');
            $this->form_validation->set_rules('shipping_address', '', 'trim');

            if ($this->form_validation->run() == TRUE) {

                $currentOrderInfo = $this->orders_model->get_data('subscription_order_list', array('id' => $this->input->post('order_id')));
                $status = $this->input->post('status');
                $orderReferenceId = $currentOrderInfo->reference_id;
                $note = $this->input->post('note');
                $orderId = $this->input->post('order_id');
                $deliveryId = $this->input->post('delivery_id') ? $this->input->post('delivery_id') : '';

                if (!empty($this->input->post('shipping_address'))) {
                    $shippingAddress = $this->input->post('shipping_address');
                } else {
                    $shippingAddress = $currentOrderInfo->shipping_address;
                }

                $this->global_model->update('subscription_order_list', array('delivery_id' => $deliveryId, 'shipping_address' => $shippingAddress), array('id' => $this->input->post('order_id')));
                $this->handleCODStatusUpdate($status, $orderId, $orderReferenceId, $note, $deliveryId);

                $response['status'] = 1;
                $response['message'] = 'Success';
            } else {
                $response['status'] = 0;
                $response['message'] = validation_errors();
            }
        } else {
            $response['status'] = 0;
            $response['message'] = 'Unknown method';
        }

        echo json_encode($response);
    }

    public function couponFormForCOD($orderId) {
        $data = array();
        $data['title'] = 'Add';
        $data['tab_active'] = 'coupon';

        $data['getCouponSeriesList'] = $this->coupon_model->getSeriesName();
        $data['getProList'] = $this->productpurchase_model->getProListAssociate();
        $data['orderId'] = $orderId;

        $this->load->view('subscription/orders/add_coupon_for_cod', $data);
    }

    public function addCouponForCOD_ajax() {

        if ($this->input->post()) {

            $sessionUserId = $this->session->userdata('userId');

            if ($_SERVER['HTTP_HOST'] == 'localhost') {
                $sessionUserId = 111;
            }

            if (!empty($sessionUserId) && ($sessionUserId != 0)) {

                $response = array();
                $this->form_validation
                        ->set_rules('series_id', 'Series Name', 'trim|required')
                        ->set_rules('note', 'Coupon Note', 'trim')
                        ->set_rules('order_id', 'Order ID', 'trim|required');

                if ($this->form_validation->run() == TRUE) {

                    $currentOrderInfo = $this->orders_model->get_data('subscription_order_list', array('id' => $this->input->post('order_id')));

                    if ($currentOrderInfo->payment_status == 'pending' && $currentOrderInfo->order_status == 'confirmed' && $currentOrderInfo->payment_method_type_id == 7) {
                        $data = array();
                        $data['series_id'] = $this->input->post('series_id');
                        $data['product_id'] = $currentOrderInfo->product_id;
                        $data['note'] = $this->input->post('note');
                        $data['created_user_id'] = $sessionUserId;
                        $data['used_by_user_id'] = $currentOrderInfo->user_id;
                        $data['order_id'] = $currentOrderInfo->reference_id;
                        $data['created_date_time'] = date('Y-m-d H:i:s');

                        //for coupon code genarate
                        $coupon_code = $this->random_string(4);
                        $match = $this->coupon_model->doesExists('subscription_coupons', array('series_id' => $this->input->post('series_id'), 'coupon' => $coupon_code));
                        if ($match != TRUE) {
                            $data['coupon'] = $coupon_code;
                            $this->db->trans_start();
                            if ($this->coupon_model->saveCoupon($data)) {
                                $status = 'coupon_generated';
                                $this->CODStatusUpdate($status, $currentOrderInfo->id, $currentOrderInfo->reference_id);
                            }
                            $this->db->trans_complete();

                            if ($this->db->trans_status() === FALSE) {
                                $response['status'] = 0;
                                $response['message'] = 'DB error occurred, Transaction failed.';
                            } else {
                                $response['status'] = 1;
                                $response['message'] = 'Coupon generated successfully';
                            }
                        } else {
                            $response['status'] = 0;
                            $response['message'] = 'Failed to generate Coupon, Try again';
                        }
                    } else {
                        $response['status'] = 0;
                        $response['message'] = 'This order is not eligible to process';
                    }
                } else {
                    $response['status'] = 0;
                    $response['message'] = validation_errors();
                }
            } else {
                $response['status'] = 0;
                $response['message'] = 'Session User Id not found';
            }
        } else {
            $response['status'] = 0;
            $response['message'] = 'Unknown method';
        }
        echo json_encode($response);
    }

    public function generateInvoiceInPDF() {

        $orderId = $this->input->get("orderId");

        $orderInfo = $this->orders_model->getOrderInfoById($orderId);

        $data['userInfo'] = $this->admin_model->getAUserInfoById($orderInfo->user_id);
        $data['productInfo'] = $this->product_model->getProductInfoById($orderInfo->product_id);

        if ($orderInfo->payment_method_type_id == 7) {
            $data['CODInfo'] = $this->orders_model->get_data('subscription_coupons', array('order_id' => $orderInfo->order_id));
            $data['seriesInfo'] = $this->orders_model->get_data('subscription_coupon_series', array('id' => $data['CODInfo']->series_id));
        } else {
            $data['CODInfo'] = '';
        }

        $data['orderInfo'] = $orderInfo;

        //$pdfData = $this->load->view('subscription/orders/invoice_pdf', $data);

        require_once './vendor/autoload.php';

        $mpdf = new mPDF('utf-8', array(210, 303), 0, 0, 0, 0, 0, 0);
        $cssPath = base_url('assets/invoice') . '/invoice.css';

        $stylesheet = file_get_contents($cssPath);
        $mpdf->WriteHTML($stylesheet, 1); // The parameter 1 tells that this is css/style only and no body/html/text
        //$html = file_get_contents('invoice.html');

        $pdfData = $this->load->view('subscription/orders/invoice_pdf', $data, TRUE);

        $mpdf->WriteHTML($pdfData, 2);
        $mpdf->Output();
    }

//    public function generateInvoiceForCustomerPDF($orderId) {
//
//        $orderInfo = $this->orders_model->getOrderInfoById($orderId);
//
//        $data['userInfo'] = $this->admin_model->getAUserInfoById($orderInfo->user_id);
//        $data['productInfo'] = $this->product_model->getProductInfoById($orderInfo->product_id);
//
//        $data['orderInfo'] = $orderInfo;
//
//        require_once './vendor/autoload.php';
//
//        $mpdf = new mPDF('utf-8', array(210, 303), 0, 0, 0, 0, 0, 0);
//        $cssPath = base_url('assets/invoice') . '/invoice.css';
//
//        $stylesheet = file_get_contents($cssPath);
//        $mpdf->WriteHTML($stylesheet, 1); // The parameter 1 tells that this is css/style only and no body/html/text
//        //$html = file_get_contents('invoice.html');
//
//        $pdfData = $this->load->view('subscription/orders/customer_pdf', $data, TRUE);
//
//        $mpdf->WriteHTML($pdfData, 2);
//        $mpdf->Output();
//    }

    public function generateInvoiceForAuthorityAndCutomerPDF() {
        
        $orderId = $this->input->get("orderId");
        $orderInfo = $this->orders_model->getOrderInfoById($orderId);

        $data['userInfo'] = $this->admin_model->getAUserInfoById($orderInfo->user_id);
        $data['productInfo'] = $this->product_model->getProductInfoById($orderInfo->product_id);

        $data['orderInfo'] = $orderInfo;

        require_once './vendor/autoload.php';

        $mpdf = new mPDF('utf-8', array(210, 303), 0, 0, 0, 0, 0, 0);
        $cssPath = base_url('assets/invoice') . '/invoice.css';

        $stylesheet = file_get_contents($cssPath);
        $mpdf->WriteHTML($stylesheet, 1);

        $pdfData = $this->load->view('subscription/orders/prizebond_pdf', $data, TRUE);

        $mpdf->WriteHTML($pdfData, 2);
        $mpdf->Output();
    }

    public function addPrizeBond($orderId) {
        $data = array();
        $data['title'] = 'Add';
        $data['orderId'] = $orderId;
        $data['prizeBondSeries'] = $this->orders_model->getPrizeBondSeriesName();
        $data['orderInfo'] = $this->orders_model->getOrderInfoById($orderId);
        $this->load->view('subscription/orders/add_prizebond', $data);
    }

    public function addPrizeBond_ajax() {
        if ($this->input->post()) {
            $seriesArray = $this->input->post('series');
            $bondArray = $this->input->post('bond_number');

            $seriesCount = count(array_filter($seriesArray));
            $bondCount = count(array_filter($bondArray));

            if ($this->input->post('totalBond') == $seriesCount && ($this->input->post('totalBond') == $bondCount)) {
                $bondListArray = array();
                for ($i = 0; $i < $this->input->post('totalBond'); $i++) {
                    $series = str_replace(' ', '', $seriesArray[$i]);
                    $bondListArray[] = $series . ' ' . $bondArray[$i];
                }
                $bondList = implode(' , ', $bondListArray);
                if ($this->global_model->update('subscription_order_list', array('prizebond' => $bondList), array('id' => $this->input->post('orderId')))) {
                    $response['status'] = 1;
                    $response['message'] = 'Prizebond added successfully';
                } else {
                    $response['status'] = 0;
                    $response['message'] = 'Field to save bond numbers';
                }
            } else {
                $response['status'] = 0;
                $response['message'] = 'Fillup all field properly';
            }
        } else {
            $response['status'] = 0;
            $response['message'] = 'Unknown method';
        }
        echo json_encode($response);
    }

    public function addPrizeBondDeliveryId($orderId) {
        $data = array();
        $data['title'] = 'Add';
        $data['orderId'] = $orderId;
        $this->load->view('subscription/orders/add_prizebond_delivery_id', $data);
    }

    public function addPrizeBondDeliveryId_ajax() {

        if ($this->input->post()) {
            $deliveryId = $this->input->post('delivery_id');

            if (!empty($deliveryId)) {
                if ($this->global_model->update('subscription_order_list', array('delivery_id' => $deliveryId), array('id' => $this->input->post('orderId')))) {
                    $response['status'] = 1;
                    $response['message'] = 'Delivery ID added successfully';
                } else {
                    $response['status'] = 0;
                    $response['message'] = 'Field to save delivery ID';
                }
            } else {
                $response['status'] = 0;
                $response['message'] = 'Delivery ID required';
            }
        } else {
            $response['status'] = 0;
            $response['message'] = 'Unknown method';
        }
        echo json_encode($response);
    }

    private function convertEnglishNumberIntoBanglaNumber($number) {
        $englishNumber = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0);
        $banglaNumber = array('১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০');
        $convertNumber = str_replace($englishNumber, $banglaNumber, $number);
        return $convertNumber;
    }

    private function random_string($length) {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }
        $coupon = strtoupper($key);

        return $coupon;
    }

    private function handleCODStatusUpdate($status, $orderId, $orderReferencId, $note = NULL, $deliveryId = NULL) {
        $allowedActions = array();
        $currentOrderStatus = $this->orders_model->get_data('subscription_order_list', array('id' => $orderId));

        if ($currentOrderStatus->payment_method_type_id == 7) {
            if (preg_match('/\s/', $status)) {
                $status = str_replace(' ', '_', $status);
            }
            $status = strtolower($status);
            if ($currentOrderStatus->order_status == 'waiting_for_confirmed') {
                // allowed = confirmed / canceled
                $allowedActions = array('confirmed', 'cancel');
            } else if ($currentOrderStatus->order_status == 'coupon_generated') {
                // allowed = shipped
                $allowedActions = array('shipped');
            } else if ($currentOrderStatus->order_status == 'shipped') {
                // allowed = delivered / wrong_shipping_address / delivery_failed
                $allowedActions = array('delivered', 'wrong_shipping_address', 'delivery_failed');
            } else if ($currentOrderStatus->order_status == 'wrong_shipping_address') {
                // allowed = shipped
                $allowedActions = array('shipped');
            } else if ($currentOrderStatus->order_status == 'delivery_failed') {
                // allowed = shipped / cancel
                $allowedActions = array('shipped', 'cancel');
            }
            if (in_array($status, $allowedActions)) {
                if ($this->CODStatusUpdate($status, $orderId, $currentOrderStatus->reference_id, $note, $deliveryId)) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    private function CODStatusUpdate($status, $orderId, $orderReferencId, $note = NULL, $deliveryId = NULL) {

        if ($this->orders_model->updateCODStatus($orderId, $status)) {
            if ($status == 'cancel') {
                $this->db->delete('subscription_coupons', array('order_id' => $orderReferencId));
            }
            $data = array();
            $data['order_id'] = $orderReferencId;
            $data['action_user_id'] = $this->session->userdata('userId') ? $this->session->userdata('userId') : '111';
            $data['message'] = 'Status : ' . $status . ', Note : ' . $note . ', Delivery ID : ' . $deliveryId;
            if ($this->db->insert('subscription_order_activity_log', $data)) {
                return TRUE;
            }
        }

        return FALSE;
    }

}
