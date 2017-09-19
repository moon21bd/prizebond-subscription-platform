<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require_once APPPATH . 'controllers/Main.php';

class Error extends Main {

    function __construct() {
        parent::__construct();
        $this->load->model('logs/Message_model');
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }

        if ($this->checkHost()) {
            $this->checkAuth();
        }
    }

    public function index() {

        $data['title'] = 'Log List';
        //$data['tabActive'] = 'logLists';
        $data['error'] = '';
        $logs = array();
        $logsFile = array();
        $logsFiles = glob(FCPATH . "application/v4.0/logs/*.php");

        foreach ($logsFiles as $file) {
            $logsFile[] = substr($file, -18);
        }
        $data['logs'] = array_reverse($logsFile);
        //$data['notificationList'] = $this->Message_model->getNotificationsData();
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('admin/logs/log_file_list', $data);
        $this->load->view('admin/footer', $data);
    }

    public function viewLogFile($fileName) {
        $data = array();
        $data['title'] = 'Log Details';
        $finalArray = array();
        $file = FCPATH . "application/v4.0/logs/" . $fileName;
        $errorArray = file($file);
        $errorArray = array_filter($errorArray);


        $totalError = 0;
        $totalNotice = 0;
        $totalWarning = 0;
        $totalPageNotFoundError = 0;
        $totalUserError = 0;
        $totalParsingError = 0;
        $totalQueryError = 0;


        foreach ($errorArray as $error) {

            if (strpos($error, "Severity: Error")) {
                //$data = array();
                $errorExploded = explode('-->', $error);
                $data['error_date'] = trim(str_replace('ERROR -', '', $errorExploded[0]));
                $data['severity'] = 'PHP Error';
                $data['message'] = trim($errorExploded[2]);

                $finalArray[] = $data;
                $totalError ++;
            } else if (strpos($error, "Severity: Notice")) {
                //$data = array();
                $errorExploded = explode('-->', $error);
                $data['error_date'] = trim(str_replace('ERROR -', '', $errorExploded[0]));
                $data['severity'] = trim(str_replace('Severity: ', '', $errorExploded[1]));
                $data['message'] = trim($errorExploded[2]);

                $finalArray[] = $data;
                $totalNotice ++;
            } else if (strpos($error, "Severity: Parsing Error")) {
                //$data = array();
                $errorExploded = explode('-->', $error);
                $data['error_date'] = trim(str_replace('ERROR -', '', $errorExploded[0]));
                $data['severity'] = trim(str_replace('Severity: ', '', $errorExploded[1]));
                $data['message'] = trim($errorExploded[2]);

                $finalArray[] = $data;
                $totalParsingError ++;
            } else if (strpos($error, "Severity: Warning")) {
                //$data = array();
                $errorExploded = explode('-->', $error);
                $data['error_date'] = trim(str_replace('ERROR -', '', $errorExploded[0]));
                $data['severity'] = trim(str_replace('Severity: ', '', $errorExploded[1]));
                $data['message'] = trim($errorExploded[2]);

                $finalArray[] = $data;
                $totalWarning ++;
            } else if (strpos($error, "Query error")) {
                //$data = array();
                $errorExploded = explode('-->', $error);
                $$data['error_date'] = trim(str_replace('ERROR -', '', $errorExploded[0]));
                $data['severity'] = 'DB Error';
                $data['message'] = trim($errorExploded[1]);

                $finalArray[] = $data;
                $totalQueryError ++;
            } else if (strpos($error, "--> 404")) {
                //$data = array();
                $errorExploded = explode('-->', $error);
                $data['error_date'] = trim(str_replace('ERROR -', '', $errorExploded[0]));
                $data['severity'] = '404';
                $data['message'] = trim($errorExploded[1]);

                $finalArray[] = $data;
                $totalPageNotFoundError ++;
            } else if (strpos($error, "-->")) {

                //$data = array();
                $errorExploded = explode('-->', $error);
                $data['error_date'] = trim(str_replace('ERROR -', '', $errorExploded[0]));
                $data['severity'] = 'user error';
                $data['message'] = trim($errorExploded[1]);

                $finalArray[] = $data;
                $totalUserError ++;

                //$totalUserError += $total;
            } else {
                
            }

            /*
              $errorArr = explode('-->', $error);
              if (count($errorArr) == 2) {
              //  $data['error_date'] = substr($errorArr[0], -19);
              $data['error_date'] = trim(str_replace('ERROR -', '', $errorArr[0]));
              $data['severity'] = '404';
              $data['message'] = $errorArr[1];
              $finalArray[] = $data;
              } elseif (count($errorArr) == 3) {
              //    $data['error_date'] = substr($errorArr[0], -21);
              $data['error_date'] = trim(str_replace('ERROR -', '', $errorArr[0]));
              $data['severity'] = trim(str_replace('Severity:', '', $errorArr[1]));
              $data['message'] = $errorArr[2];
              $finalArray[] = $data;
              } */
        }
        $data['totalError'] = $totalError;
        $data['totalNotice'] = $totalNotice;
        $data['totalWarning'] = $totalWarning;
        $data['totalPageNotFound'] = $totalPageNotFoundError;
        $data['totalUserError'] = $totalUserError;
        $data['totalParsingError'] = $totalParsingError;
        $data['totalQueryError'] = $totalQueryError;


        $data['file_name'] = $fileName;


        $data['logs'] = array_reverse($finalArray);


        //$logErrorArray['notificationList'] = $this->Message_model->getNotificationsData();
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('admin/logs/error_details', $data);
        $this->load->view('admin/footer', $data);
    }

    public function deleteLogFile($fileName) {
        $file = FCPATH . "application/v4.0/logs/" . $fileName;
        unlink($file);
        redirect('logs/error');
    }

    public function deleteLogError($fileName) {
        $file = FCPATH . "application/v4.0/logs/" . $fileName;
        $fp = fopen($file, "w");
        fclose($fp);
        redirect('logs/error/viewLogFile/' . $fileName);
    }

}
