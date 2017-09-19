<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-05-14
 */

set_time_limit(0);
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {

        $data["orders"] = $this->generateOrderHistory();
        $data["users_data"] = $this->userActivity();
        $data["purchases"] = $this->purchaseHistory();

//        echo "<pre>";
//        print_r($data);
//        die();

        $data = array();
        $this->load->view('admin/dashboard_porosh', $data);
    }

    public function serverSentEvent() {

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');

        //$time = date('r');
        //echo "data: The server time is: {$time}\n\n";
        /*
          $data["orders"]["labels"] = array("10 am", "11 am", "12 am", "10 am", "10 am", "10 am", "10 am");
          $data["orders"]["data"]["pending"] = array(2, rand(50, 90),56,12,67,45,87);
          $data["orders"]["data"]["success"] = array(2,23,56,12,rand(10, 90),45,87);
          $data["orders"]["data"]["failed"] = array(rand(20, 90),23,56,12,67,45,87); */

        $data["orders"] = $this->generateOrderHistory();
        $data["users_data"] = $this->userActivity();
        $data["purchases"] = $this->purchaseHistory();


        echo 'data: ' . json_encode($data) . "\n\n";

        flush();
    }

    private function generateOrderHistory() {

        $data = array();
        $orderStatus = array(
            'pending',
            'completed',
            'failed'
        );

        $minArray = array(
            60,
            50,
            40,
            30,
            20,
            10,
            0
        );

        foreach ($minArray as $min) {
          
            $labels[] = date("g:i a", strtotime("-$min minute"));
          
            $queryStartDate = date("Y-m-d H:i:s", strtotime("-$min minutes"));
            $queryEndDate = date("Y-m-d H:i:s", strtotime("+5 minutes", strtotime($queryStartDate)));

            foreach ($orderStatus as $status) {
                //$sql = "SELECT COUNT(id) AS TOTAL FROM subscription_order_list WHERE `order_status`='$status' AND ((`order_created_date_time` > DATE_SUB(NOW(), INTERVAL $min MINUTE)) AND (`order_created_date_time` < NOW()))";
                $sql = "SELECT COUNT(id) AS TOTAL FROM subscription_order_list WHERE `order_status`='$status' AND (`order_created_date_time` >= '$queryStartDate' AND `order_created_date_time` <= '$queryEndDate')";

                $query = $this->db->query($sql);
                foreach ($query->result() as $row) {
                    $data["data"][$status][] = $row->TOTAL;
                }
            }
        }

        $data["labels"] = $labels;
        return $data;
    }

    private function userActivity() {

        $data = array();

        $minArray = array(
            60,
            50,
            40,
            30,
            20,
            10,
            0
        );

        foreach ($minArray as $min) {

            $labels[] = date("g:i a", strtotime("-$min minute"));

            $queryStartDate = date("Y-m-d H:i:s", strtotime("-$min minutes"));
            $queryEndDate = date("Y-m-d H:i:s", strtotime("+5 minutes", strtotime($queryStartDate)));

            //$endDate = date("Y-m-d H:i:s");
            //SELECT COUNT(id) AS TOTAL FROM user_activity_logs WHERE `created_date_time` >= '2017-05-07 20:00' AND `created_date_time` <= '2017-05-07 20:05'
            //$sql = "SELECT COUNT(id) AS TOTAL FROM user_activity_logs WHERE ((`created_date_time` > DATE_SUB(NOW(), INTERVAL $min MINUTE)) AND (`created_date_time` < NOW()))";
            $sql = "SELECT COUNT(id) AS TOTAL FROM user_activity_logs WHERE `created_date_time` >= '$queryStartDate' AND `created_date_time` <= '$queryEndDate'";
            $query = $this->db->query($sql);
            foreach ($query->result() as $row) {
                $data["data"]["activity"][] = $row->TOTAL;
            }

            //$sql = "SELECT COUNT(id) AS TOTAL FROM user WHERE ((`created_date_time` > DATE_SUB(NOW(), INTERVAL $min MINUTE)) AND (`created_date_time` < NOW()))";
            $sql = "SELECT COUNT(id) AS TOTAL FROM user WHERE `created_date_time` >= '$queryStartDate' AND `created_date_time` <= '$queryEndDate'";

            $query = $this->db->query($sql);
            foreach ($query->result() as $row) {
                $data["data"]["registration"][] = $row->TOTAL;
            }

            //$sql = "SELECT COUNT(id) AS TOTAL FROM user WHERE `verify_status` = 'YES' AND ((`created_date_time` > DATE_SUB(NOW(), INTERVAL $min MINUTE)) AND (`created_date_time` < NOW()))";
            $sql = "SELECT COUNT(id) AS TOTAL FROM user WHERE `verify_status` = 'YES' AND (`created_date_time` >= '$queryStartDate' AND `created_date_time` <= '$queryEndDate')";
            $query = $this->db->query($sql);
            foreach ($query->result() as $row) {
                $data["data"]["verification"][] = $row->TOTAL;
            }

            //$sql = "SELECT COUNT(id) AS TOTAL FROM user_prizebond_list WHERE ((`created_date_time` > DATE_SUB(NOW(), INTERVAL $min MINUTE)) AND (`created_date_time` < NOW()))";
            $sql = "SELECT COUNT(id) AS TOTAL FROM user_prizebond_list WHERE (`created_date_time` >= '$queryStartDate' AND `created_date_time` <= '$queryEndDate')";
            $query = $this->db->query($sql);
            foreach ($query->result() as $row) {
                $data["data"]["bonds_added"][] = $row->TOTAL;
            }
        }

        $data["labels"] = $labels;
        return $data;
    }

    private function purchaseHistory() {
        //SELECT `purchased_by`, COUNT(id) AS TOTAL FROM `subscription_product_purchase_list` WHERE product_id != 1 AND (`created_datetime` >= '2017-04-08 17:00:00' AND `created_datetime` <= '2017-05-08 17:20:00') GROUP BY `purchased_by`


        $minArray = array(
            60,
            50,
            40,
            30,
            20,
            10,
            0
        );

        $data = array();

        $data["data"] = array();
        foreach ($minArray as $min) {

            $labels[] = date("g:i a", strtotime("-$min minute"));

            $queryStartDate = date("Y-m-d H:i:s", strtotime("-$min minutes"));
            $queryEndDate = date("Y-m-d H:i:s", strtotime("+5 minutes", strtotime($queryStartDate)));

            $sql = "SELECT `purchased_by`, COUNT(id) AS TOTAL FROM `subscription_product_purchase_list` WHERE product_id != 1 AND (`created_datetime` >= '2017-04-08 17:00:00' AND `created_datetime` <= '2017-05-08 17:20:00') GROUP BY `purchased_by`";
//           echo $sql = "SELECT `purchased_by`, COUNT(id) AS TOTAL FROM `subscription_product_purchase_list` WHERE product_id != 1 AND (`created_datetime` >= '$queryStartDate' AND `created_datetime` <= '$queryEndDate') GROUP BY `purchased_by`";

            $query = $this->db->query($sql);
            foreach ($query->result() as $row) {
                $data["data"][$row->purchased_by][] = $row->TOTAL;
            }
        }

        $data["labels"] = $labels;
        return $data;
    }

}
