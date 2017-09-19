<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

set_time_limit(0);
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require 'Main.php';

class Export extends Main {

    public function __construct() {
        parent::__construct();
        if ($this->checkHost()) {
            $this->checkAuth();
        }
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
    }

    public function csv($reportQuery) {
        $sql = $this->session->userdata($reportQuery);
        $sql = explode('LIMIT', $sql);
        $sql = $sql[0];

        $query = $this->db->query($sql);
        $reports = $query->result_array();
        
        $startDate = $this->session->userdata('start_date');
        $endDate = $this->session->userdata('end_date');

        foreach ($reports as $array) {
            $arrayHeadLine = array_keys($array);
            $dataArray[] = implode(",", $arrayHeadLine);
            break;
        }

        if (is_array($reports) && count($reports)) {
            foreach ($reports as $report) {
                $dataArray[] = implode(",", $report);
            }
        }

        $filename = $reportQuery."_report_" . $startDate . "_to_" . $endDate . ".csv";

        header('Content-Type: application/excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $fp = fopen('php://output', 'w');
        foreach ($dataArray as $line) {
            $val = explode(",", $line);
            fputcsv($fp, $val);
        }
        fclose($fp);
    }

}

?>