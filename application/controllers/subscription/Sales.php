<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-09-05
 */

defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/Main.php';

class Sales extends Main {

    public function __construct() {
        parent::__construct();
        if ($this->checkHost()) {
            $this->checkAuth();
        }

        if ($this->input->get('debug') == 1) {
            $this->output->enable_profiler(TRUE);
        }

        $this->load->model('subscription/productpurchase_model');
        $this->load->model('report/sales_model');
        $this->load->model('subscription/orders_model');
        $this->load->helper('user_profile_info_helper');
    }

    public function __destruct() {
        $this->db->close();
    }

    public function summary() {

        $paymentMethod = NULL;
        $productListId = NULL;
        $startDate = $this->session->userdata('start_date');
        $endDate = $this->session->userdata('end_date');

        if ($this->input->get()) {
            $paymentMethod = trim($this->input->get('payment_method'));
            $productListId = trim($this->input->get('product_id'));
        }

        if ($this->input->post("Download_CSV")) {

            $reports = $this->sales_model->getSalesReports($startDate, $endDate, $paymentMethod, $productListId);
            $dataArray[] = implode(",", array("payment_method", "product_name", "total_amount", "total_sales"));

            if (is_array($reports) && count($reports)) {
                foreach ($reports as $report) {

                    foreach ($report as $data) {
                        $row = array($data["payment_method"], $data["product_info"]["product_name"], $data["total_amount"], $data["total_sales"]);
                        $dataArray[] = implode(",", $row);
                    }
                }
            }

            $filename = "sales_summary_report_" . $startDate . "_to_" . $endDate . ".csv";

            header('Content-Type: application/excel');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $fp = fopen('php://output', 'w');
            foreach ($dataArray as $line) {
                $val = explode(",", $line);
                fputcsv($fp, $val);
            }
            fclose($fp);
        } else {

            $data = array();
            $data['title'] = 'Sales Summary';
            $data['paymentMethodArray'] = $this->productpurchase_model->getPurchaseMethod();

            $data['productListArray'] = $this->productpurchase_model->getProductList();

            $data['reports'] = $this->sales_model->getSalesReports($startDate, $endDate, $paymentMethod, $productListId);

            $this->load->view('admin/header', $data);
            $this->load->view('admin/navbar', $data);
            $this->load->view('admin/sidebar', $data);
            $this->load->view('subscription/sales/summary', $data);
            $this->load->view('admin/footer', $data);
        }
    }

    public function history() {

        $data = array();
        $data['title'] = 'Sales History';
        //$data['tab_active'] = 'orderlist';
        $data['paymentMethod'] = $this->orders_model->getPaymentMethod();
        $data['paymentSelect'] = '';





        $orderArray = array('total_receivable_amount','order_created_date_time');
        $orderBy = '';
        $order = '';
        if (isset($_GET['orderBy']) && in_array($_GET['orderBy'], $orderArray)) {
            $orderBy = $_GET['orderBy'];
            $order = $_GET['order'];
            
        }



       
        

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
                $this->form_validation->set_rules('product', '', 'trim');
            }
            if ($this->form_validation->run() == TRUE) {
                $searchQuery = trim($this->input->get('search_query'));
                $paymentName = trim($this->input->get('payment_name'));
                $product = trim($this->input->get('product'));
            } else {
                echo validation_errors();
            }
        }

        if ($this->input->post("Download_CSV")) {
            $orderListInfo = $this->orders_model->getOrderList($userId, $limit, $offset, $startDate, $endDate, $searchQuery, $paymentName, 'success', 'completed', $product, $orderBy, $order);

            $dataArray[] = implode(",", array("date", "order_id", "user_name", "user_id", "product_name", "product_price", "payment_method", "amount"));

            $orderList = $orderListInfo['result'];

            if (is_array($orderList) && count($orderList)) {

                foreach ($orderList as $data) {
                    $row = array($data["order_created_date_time"], $data["order_id"], $data["user_name"], $data["user_externel_id"], $data['product_name'], $data['price'], $data['payment_method_type_name'], $data['total_receivable_amount']);
                    $dataArray[] = implode(",", $row);
                }
            }

            $filename = "sales_history_report_" . $startDate . "_to_" . $endDate . ".csv";

            header('Content-Type: application/excel');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $fp = fopen('php://output', 'w');
            foreach ($dataArray as $line) {
                $val = explode(",", $line);
                fputcsv($fp, $val);
            }
            fclose($fp);
        } else {

            $orderListInfo = $this->orders_model->getOrderList($userId, $limit, $offset, $startDate, $endDate, $searchQuery, $paymentName, 'success', 'completed', $product, $orderBy, $order);

            if ($this->session->userdata('userRole') == Main::USER_ROLE_CRM) {
                if ($this->input->get()) {
                    $data['orderList'] = $orderListInfo['result'];
                    $data['total'] = $total_rows = $orderListInfo['total'];
                    $data['totalAmount'] = $orderListInfo['totalAmount'];
                    $data['totalBkashSalesAmount'] = $this->orders_model->getTotalBkashSalesAmount($startDate, $endDate);
                    $data['totalBkashSales'] = $this->orders_model->getTotalBkashSales($startDate, $endDate);
                    $data['totalSales'] = $this->orders_model->getTotalSalesAmount($startDate, $endDate);
                    $data['totalBracSalesAmount'] = $this->orders_model->getTotalBracSalesAmount($startDate, $endDate);
                    $data['totalCodSalesAmount'] = $this->orders_model->getTotalCodSalesAmount($startDate, $endDate);
                    $data['totalCodSales'] = $this->orders_model->getTotalCodSales($startDate, $endDate);
                } else {
                    $data['orderList'] = '';
                    $total_rows = 0;
                }
            } else {
                $data['orderList'] = $orderListInfo['result'];
                $data['total'] = $total_rows = $orderListInfo['total'];
                $data['totalAmount'] = $orderListInfo['totalAmount'];
                $data['totalBkashSalesAmount'] = $this->orders_model->getTotalBkashSalesAmount($startDate, $endDate);
                $data['totalBkashSales'] = $this->orders_model->getTotalBkashSales($startDate, $endDate);
                $data['totalSales'] = $this->orders_model->getTotalSalesAmount($startDate, $endDate);
                $data['totalBracSalesAmount'] = $this->orders_model->getTotalBracSalesAmount($startDate, $endDate);
                $data['totalBracSales'] = $this->orders_model->getTotalBracSales($startDate, $endDate);
                $data['totalCodSalesAmount'] = $this->orders_model->getTotalCodSalesAmount($startDate, $endDate);
                $data['totalCodSales'] = $this->orders_model->getTotalCodSales($startDate, $endDate);
            }


            generatePagging('/subscription/sales/history/offset/', $total_rows, $limit, $uri_segment, 4);

            $this->load->view('admin/header', $data);
            $this->load->view('admin/navbar', $data);
            $this->load->view('admin/sidebar', $data);
            $this->load->view('subscription/sales/history', $data);
            $this->load->view('subscription/footer', $data);
        }
    }

}
