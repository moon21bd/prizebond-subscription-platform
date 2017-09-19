<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-09-19
 */

defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/Main.php';

class Reports extends Main {

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
        $this->load->helper('user_profile_info_helper');
    }

    public function __destruct() {
        $this->db->close();
    }

    public function sales() {

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

            $filename = "sales_report_" . $startDate . "_to_" . $endDate . ".csv";

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
            $data['title'] = 'Sales Reports';
            $data['paymentMethodArray'] = $this->productpurchase_model->getPurchaseMethod();

            $data['productListArray'] = $this->productpurchase_model->getProductList();

            $data['reports'] = $this->sales_model->getSalesReports($startDate, $endDate, $paymentMethod, $productListId);

            $this->load->view('admin/header', $data);
            $this->load->view('admin/navbar', $data);
            $this->load->view('admin/sidebar', $data);
            $this->load->view('subscription/reports/sales', $data);
            $this->load->view('admin/footer', $data);
        }
    }

    public function salesReport() {
        $data = array();
        $startDate = $this->session->userdata('start_date');
        $endDate = $this->session->userdata('end_date');
        $data['title'] = 'Report';


        $data['productListArray'] = $this->productpurchase_model->getProductList();

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/reports/sales_reports', $data);
        $this->load->view('admin/footer', $data);
    }

    public function gatewayReport() {
        $data = array();
        $data['title'] = 'Report';
        $data['transactionInfoArray'] = '';
        $data['total_rows'] = '';
        $data['total_trx'] = '';
        $data['total_amount'] = '';
        $data['total_refund'] = '';
        $data['total_chargeback'] = '';
        $data['total_fee'] = '';
        $data['total_final_amount'] = '';
        $data['gatewayArray'] = $this->sales_model->geGatewayName();

        $gateway = NULL;
        $transcation_type = NULL;
        $transcation_status = NULL;
        $compare_amount = NULL;
        $compaire_final_amount = NULL;
        $compare = '';
        $amount = '';
        $compaire_final = '';
        $final_amount = '';
        $startDate = $this->session->userdata('start_date') ? $this->session->userdata('start_date') : date("Y-m-d", strtotime("- 30 days"));
        $endDate = $this->session->userdata('end_date') ? $this->session->userdata('end_date') : date("Y-m-d");

        if ($this->input->get()) {

            $gateway = trim($this->input->get('gateway'));
            $transcation_type = trim($this->input->get('transcation_type'));
            $transcation_status = trim($this->input->get('transcation_status'));
            if ($this->input->get('compaire_amount') != 0) {
                $compare = $this->input->get('compaire_amount');
                $amount = $this->input->get('amount');
                $compare_amount = $compare . ' ' . $amount;
            }

            if ($this->input->get('compaire_final_amount') != 0) {
                $compaire_final = trim($this->input->get('compaire_final_amount'));
                $final_amount = trim($this->input->get('final_amount'));
                $compaire_final_amount = $compaire_final . ' ' . $final_amount;
            }
        }

        $transactions = $this->sales_model->getTransactionInfo($startDate, $endDate, $gateway, $transcation_type, $transcation_status, $compare_amount, $compaire_final_amount);
        if (is_array($transactions) && (count($transactions) > 0)) {
            $data['transactionInfoArray'] = $transactions['result'];
            $data['total_rows'] = $transactions['total_row'];
            $data['total_trx'] = $transactions['total_trx'];
            $data['total_amount'] = $transactions['total_amount'];
//            $data['total_refund'] = $transactions['total_refund'];
//            $data['total_chargeback'] = $transactions['total_chargeback'];
            $data['total_fee'] = $transactions['total_fee'];
            $data['total_final_amount'] = $transactions['total_final_amount'];
        }


        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/reports/gateway_reports', $data);
        $this->load->view('admin/footer', $data);
    }

    public function viewGatewayInfo($id) {
        $data = array();

//        $gatewayInfo = $this->sales_model->geGatewayInfoById($id);
//        $data['productInfo'] = $this->product_model->getProductInfoById($gatewayInfo->product_id);
//        $data['gatewayInfo'] = $gatewayInfo;
        $this->load->view('subscription/reports/gateway_info', $data);
    }

    public function recieve() {
        $data = array();
        $data['title'] = 'Report';

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/reports/gateway_report_recieve', $data);
        $this->load->view('admin/footer', $data);
    }

    public function refund() {
        $data = array();
        $data['title'] = 'Report';

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/reports/gateway_report_refund', $data);
        $this->load->view('admin/footer', $data);
    }

    public function chargeBack() {
        $data = array();
        $data['title'] = 'Report';

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/reports/gateway_report_chargeBack', $data);
        $this->load->view('admin/footer', $data);
    }

    public function income() {
        $data = array();
        $this->session->unset_userdata("attachments");
        $files = glob('./assets/media/income_expense/tmp/*'); // get all file names
        if (count($files)) {
            foreach ($files as $file) { // iterate files
                if (is_file($file))
                    unlink($file); // delete file
            }
        }

        $data['title'] = 'Report';
        $data['paymentSourceArray'] = $this->sales_model->getPaymentMethod();

        $startDate = $this->session->userdata('start_date') ? $this->session->userdata('start_date') : date("Y-m-d", strtotime("- 30 days"));
        $endDate = $this->session->userdata('end_date') ? $this->session->userdata('end_date') : date("Y-m-d");

        $searchSysmetmId = '';
        $searchReferenceId = '';
        $compare = '';
        $amount = '';
        $compare_amount = '';

        if ($this->input->get()) {
            if ($this->input->get('search_sysmetm_id')) {

                $searchSysmetmId = $this->input->get('search_sysmetm_id');
            }
            if ($this->input->get('search_refence_id')) {

                $searchReferenceId = $this->input->get('search_refence_id');
            }
            if ($this->input->get('compaire_amount')) {

                $compare = $this->input->get('compaire_amount');
                $amount = $this->input->get('amount');
                $compare_amount = $compare . ' ' . $amount;
            }
        }

        $getIncomeInfoArray = $this->sales_model->getIncomeInfo($startDate, $endDate, $searchSysmetmId, $searchReferenceId, $compare_amount);

        $data['IncomeInfoArray'] = $getIncomeInfoArray['result'];
        $data['total_rows'] = $getIncomeInfoArray['total_row'];
        $data['total_amount'] = $getIncomeInfoArray['total_amount'];


        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/reports/income', $data);
        $this->load->view('admin/footer', $data);
    }

    public function downloadZip($systemId, $files) {

        $files = base64_decode($files);
        $files = $this->encryption->decrypt($files);
        $fileArray = explode(',', $files);

        //create zip file
        $zip = new ZipArchive();
        $zip_name = $systemId . ".zip"; // Zip name
        $zip->open($zip_name, ZipArchive::CREATE);
        foreach ($fileArray as $file) {
            $path = 'assets/media/income_expense/' . $file;
            if (file_exists($path)) {
                $zip->addFromString(basename($path), file_get_contents($path));
            } else {
                echo"file does not exist";
            }
        }
        $zip->close();

        //download zip
        $filename = $zip_name;

        if (file_exists($zip_name)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            header('Content-Length: ' . filesize($filename));

            flush();
            readfile($filename);
            // delete file
            unlink($filename);
        }
    }

    public function fileUploadAjax() {
        $data = array();
        $fileInfo = array();
        header("Content-type:application/json");


        if (!empty($_FILES['files']['name'])) {
            $filesCount = count($_FILES['files']['name']);
            for ($i = 0; $i < $filesCount; $i++) {

                $_FILES['files']['name'] = $_FILES['files']['name'][$i];
                $_FILES['files']['type'] = $_FILES['files']['type'][$i];
                $_FILES['files']['tmp_name'] = $_FILES['files']['tmp_name'][$i];
                $_FILES['files']['error'] = $_FILES['files']['error'][$i];
                $_FILES['files']['size'] = $_FILES['files']['size'][$i];

                $uploadPath = './assets/media/income_expense/tmp/';
                $config['upload_path'] = $uploadPath;
                $config['allowed_types'] = '*';
                $config['file_name'] = uniqid();
                $config['remove_spaces'] = true;

                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                if ($this->upload->do_upload('files')) {
                    $fileData = $this->upload->data();
                    $fileInfo['original_name'] = $fileData['client_name'];
                    $fileInfo['file_name'] = $fileData['file_name'];
                    $fileInfo['file_url'] = base_url('assets/media/income_expense/tmp/' . $fileData['file_name']);
                } else {
                    $data['error'] = $this->upload->display_errors();
                }
            }
            if (count($fileInfo)) {

                if ($this->session->attachments) {
                    $fileInfoList = $this->session->attachments;
                    $fileInfoList[] = $fileInfo;
                    $this->session->set_userdata('attachments', $fileInfoList);
                } else {
                    $fileInfoList[] = $fileInfo;
                    $this->session->set_userdata('attachments', $fileInfoList);
                }

                $data['results'] = $fileInfo;
            }
//            $data['data']['results'] = 'hello';
            echo json_encode($data);
        }
    }

    public function addIncomeAjax() {
        $data = array();
        if ($this->input->post()) {

            $this->form_validation
                    ->set_rules('income_date', 'Date', 'trim|required')
                    ->set_rules('paymentSource', 'Payment Source', 'trim')
                    ->set_rules('income_description', 'Income Description', 'trim')
                    ->set_rules('income_amonut', 'Income Amount', 'trim|required')
                    ->set_rules('income_reference_id', 'Income Reference ID', 'trim|required')
                    ->set_rules('income_reference_remark', 'Income Reference Remarks', 'trim');
            if ($this->form_validation->run()) {
                $save_data = array();
                $fileAttachmentArray = $this->session->attachments;

                if (is_array($fileAttachmentArray) && !empty($fileAttachmentArray)) {
                    foreach ($fileAttachmentArray as $fileAttachment) {
                        rename('./assets/media/income_expense/tmp/' . $fileAttachment['file_name'], './assets/media/income_expense/' . $fileAttachment['file_name']);
                        $data[] = $fileAttachment['file_name'];
                    }
                }
                $file_name = implode(',', $data);

                $save_data['created_date_time'] = $this->input->post('income_date');
                $save_data['payment_source'] = $this->input->post('paymentSource');
                $save_data['description'] = $this->input->post('income_description');
                $save_data['amount'] = $this->input->post('income_amonut');
                $save_data['reference_id'] = $this->input->post('income_reference_id');
                $save_data['reference_remark'] = $this->input->post('income_reference_remark');
                $save_data['attatchment'] = $file_name;

                $save_data['system_id'] = uniqid();
                $save_data['status'] = 'income';

                if ($insert_id = $this->sales_model->saveData('income_expense_list', $save_data)) {
                    $response['status'] = 1;
                    $response['message'] = 'Income Info Added Successfully';
                } else {
                    $response['status'] = 0;
                    $response['message'] = 'Income Info Could Not Added';
                }
            } else {
                $response['status'] = 0;
                $response['message'] = validation_errors();
            }
            echo json_encode($response);
        }
    }

    public function addExpenseAjax() {
        if ($this->input->post()) {

            $this->form_validation
                    ->set_rules('expense_date', 'Date', 'trim|required')
                    ->set_rules('paymentSource', 'Payment Source', 'trim')
                    ->set_rules('expense_description', 'Income Description', 'trim')
                    ->set_rules('expense_amonut', 'Income Amount', 'trim|required')
                    ->set_rules('expense_reference_id', 'Income Reference ID', 'trim|required')
                    ->set_rules('expense_reference_remark', 'Income Reference Remarks', 'trim');
            if ($this->form_validation->run()) {
                $save_data = array();

                $fileAttachmentArray = $this->session->attachments;

                if (is_array($fileAttachmentArray) && !empty($fileAttachmentArray)) {
                    foreach ($fileAttachmentArray as $fileAttachment) {
                        rename('./assets/media/income_expense/tmp/' . $fileAttachment['file_name'], './assets/media/income_expense/' . $fileAttachment['file_name']);
                        $data[] = $fileAttachment['file_name'];
                    }
                }
                $file_name = implode(',', $data);

                $save_data['created_date_time'] = $this->input->post('expense_date');
                $save_data['payment_source'] = $this->input->post('paymentSource');
                $save_data['description'] = $this->input->post('expense_description');
                $save_data['amount'] = $this->input->post('expense_amonut');
                $save_data['reference_id'] = $this->input->post('expense_reference_id');
                $save_data['reference_remark'] = $this->input->post('expense_reference_remark');
                $save_data['system_id'] = uniqid();
                $save_data['status'] = 'expense';
                $save_data['attatchment'] = $file_name;

                if ($insert_id = $this->sales_model->saveData('income_expense_list', $save_data)) {
                    $response['status'] = 1;
                    $response['message'] = 'Expense Info Added Successfully';
                } else {
                    $response['status'] = 0;
                    $response['message'] = 'Expense Info Could Not Added';
                }
            } else {
                $response['status'] = 0;
                $response['message'] = validation_errors();
            }
            echo json_encode($response);
        }
    }

    public function expense() {
        $data = array();
        $data['title'] = 'Report';
        $startDate = $this->session->userdata('start_date') ? $this->session->userdata('start_date') : date("Y-m-d", strtotime("- 60 days"));
        $endDate = $this->session->userdata('end_date') ? $this->session->userdata('end_date') : date("Y-m-d");
        $this->session->unset_userdata("attachments");
        $files = glob('./assets/media/income_expense/tmp/*'); // get all file names
        if (count($files)) {
            foreach ($files as $file) { // iterate files
                if (is_file($file))
                    unlink($file); // delete file
            }
        }

        $data['gatewayArray'] = $this->sales_model->getPaymentMethod();
        $data['paymentSourceArray'] = $this->sales_model->getPaymentMethod();
        $searchSysmetmId = '';
        $searchReferenceId = '';
        $compare = '';
        $amount = '';
        $compare_amount = '';



        if ($this->input->get()) {
            if ($this->input->get('search_sysmetm_id')) {

                $searchSysmetmId = $this->input->get('search_sysmetm_id');
            }
            if ($this->input->get('search_refence_id')) {

                $searchReferenceId = $this->input->get('search_refence_id');
            }
            if ($this->input->get('compaire_amount')) {

                $compare = $this->input->get('compaire_amount');
                $amount = $this->input->get('amount');
                $compare_amount = $compare . ' ' . $amount;
            }
        }

        $getExpenseInfoArray = $this->sales_model->getExpenseInfo($startDate, $endDate, $searchSysmetmId, $searchReferenceId, $compare_amount);
        $data['expenseInfoArray'] = $getExpenseInfoArray['result'];
        $data['total_rows'] = $getExpenseInfoArray['total_row'];
        $data['total_amount'] = $getExpenseInfoArray['total_amount'];


        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/reports/expense', $data);
        $this->load->view('admin/footer', $data);
    }

    public function profitLoss() {
        $data = array();
        $data['title'] = 'Report';
        $startDate = $this->session->userdata('start_date') ? $this->session->userdata('start_date') : date("Y-m-d", strtotime("- 60 days"));
        $endDate = $this->session->userdata('end_date') ? $this->session->userdata('end_date') : date("Y-m-d");

        $getIncomeExpenseInfoArray = $this->sales_model->getIncomeExpenseInfo($startDate, $endDate);
        $data['getIncomeExpenseInfoArray'] = $getIncomeExpenseInfoArray['result'];
        $data['total_rows'] = $getIncomeExpenseInfoArray['total_row'];
        $data['total_amount'] = $getIncomeExpenseInfoArray['total_amount'];
        $data['total_expense'] = $getIncomeExpenseInfoArray['total_expense'];
        $data['total_income'] = $getIncomeExpenseInfoArray['total_income'];



        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/reports/profit_loss', $data);
        $this->load->view('admin/footer', $data);
    }

}
