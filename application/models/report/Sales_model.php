<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-09-19
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Sales_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function getSalesReports($startDate, $endDate, $paymentMethod, $productListId) {
        $sales = array();
        $report = array();

        $this->db->select('product_id,user_id,purchased_by')->from('subscription_product_purchase_list')
                ->where('product_id !=', 1)
                ->where('DATE(created_datetime) >=', $startDate)
                ->where('DATE(created_datetime) <=', $endDate);
        if ($paymentMethod != NULL) {
            $this->db->where('purchased_by', $paymentMethod);
        }
        if ($productListId != NULL) {
            $this->db->where('product_id', $productListId);
        }
        $query = $this->db->get();
//        $sql = "SELECT product_id,user_id,purchased_by FROM subscription_product_purchase_list WHERE product_id !='1' AND (DATE(created_datetime) >='$startDate' AND DATE(created_datetime) <='$endDate')";
//        $query = $this->db->query($sql);  
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $sales[$row->product_id][$row->purchased_by][] = $row->user_id;
            }
        }
        if (is_array($sales) && count($sales)) {
            $i = 0;
            foreach ($sales as $productId => $paymentMethodDataArray) {

                $sql = "SELECT name FROM subscription_product_list WHERE id='$productId'";
                $query = $this->db->query($sql);
                foreach ($query->result() as $row) {

                    $productInfo = array(
                        'product_id' => $productId,
                        'product_name' => $row->name
                    );

                    foreach ($paymentMethodDataArray as $paymentMethodName => $userIdArray) {
                        $info = array();
                        $totalAmount = 0;
                        $totalSales = 0;
                        foreach ($userIdArray as $userId) {
                            $sql2 = "SELECT value FROM subscription_product_attribute_values WHERE product_id='$productId' AND attribute_id='5'";
                            $query2 = $this->db->query($sql2);

                            foreach ($query2->result() as $row2) {
                                $productInfo["product_price"] = $row2->value;

                                $info[] = array(
                                    'user_id' => $userId,
                                    'price' => $row2->value
                                );
                                $totalAmount = $totalAmount + $row2->value;
                                $totalSales++;
                            }
                        }

                        $report[$paymentMethodName][$productId]['payment_method'] = $paymentMethodName;
                        $report[$paymentMethodName][$productId]['product_info'] = $productInfo;
                        $report[$paymentMethodName][$productId]['sales_info'] = $info;
                        $report[$paymentMethodName][$productId]['total_amount'] = $totalAmount;
                        $report[$paymentMethodName][$productId]['total_sales'] = $totalSales;
                    }
                }
            }
            return $report;
        }
        return FALSE;
    }

    public function saveData($table, $data = array()) {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    public function getIncomeInfo($startDate = NULL, $endDate = NULL, $searchSysmetmId = '', $searchReferenceId = '', $compare_amount = '') {
        $this->db->select('*')->from('income_expense_list')->where('status', 'income')
                ->where('DATE(created_date_time) >=', $startDate)
                ->where('DATE(created_date_time) <=', $endDate);
        if (!empty($searchSysmetmId)) {
            $this->db->where("(system_id LIKE '%$searchSysmetmId%')");
        }
        if (!empty($searchReferenceId)) {
            $this->db->where("(reference_id LIKE '%$searchReferenceId%')");
        }
        if (!empty($compare_amount)) {
            $this->db->where("(amount $compare_amount)");
        }
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        $this->session->set_userdata("income_query", $this->db->last_query());
        if ($query->num_rows() > 0) {
            // return $query->result();
            $data = array();
            $result = array();
            $totalAmount = 0;
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['description'] = $row->description;
                $result[$i]['payment_source'] = $row->payment_source;
                $result[$i]['amount'] = $row->amount;
                $result[$i]['system_id'] = $row->system_id;
                if ($row->status == 'income') {
                    $totalAmount = $totalAmount + $row->amount;
                }
                $result[$i]['reference_id'] = $row->reference_id;
                $result[$i]['reference_remark'] = $row->reference_remark;
                $result[$i]['attatchment'] = $row->attatchment;
                $result[$i]['created_date_time'] = $row->created_date_time;
                $result[$i]['updated_date_time'] = $row->updated_date_time;
                $i++;
            }

            $data['result'] = $result;
            $data['total_row'] = count($result);
            $data['total_amount'] = $totalAmount;
            return $data;
        } else {
            return FALSE;
        }
    }

    public function getExpenseInfo($startDate = NULL, $endDate = NULL, $searchSysmetmId = '', $searchReferenceId = '', $compare_amount = '') {
        $this->db->select('*')->from('income_expense_list')->where('status', 'expense')
                ->where('DATE(created_date_time) >=', $startDate)
                ->where('DATE(created_date_time) <=', $endDate);
        if (!empty($searchSysmetmId)) {
            $this->db->where("(system_id LIKE '%$searchSysmetmId%')");
        }
        if (!empty($searchReferenceId)) {
            $this->db->where("(reference_id LIKE '%$searchReferenceId%')");
        }
        if (!empty($compare_amount)) {
            $this->db->where("(amount $compare_amount)");
        }
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();

        $this->session->set_userdata("expense_query", $this->db->last_query());
        if ($query->num_rows() > 0) {
            // return $query->result();
            $data = array();
            $result = array();
            $totalAmount = 0;
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['payment_source'] = $row->payment_source;
                $result[$i]['description'] = $row->description;
                $result[$i]['amount'] = $row->amount;
                $result[$i]['system_id'] = $row->system_id;
                if ($row->status == 'expense') {
                    $totalAmount = $totalAmount + $row->amount;
                }
                $result[$i]['reference_id'] = $row->reference_id;
                $result[$i]['reference_remark'] = $row->reference_remark;
                $result[$i]['attatchment'] = $row->attatchment;
                $result[$i]['created_date_time'] = $row->created_date_time;
                $result[$i]['updated_date_time'] = $row->updated_date_time;
                $i++;
            }

            $data['result'] = $result;
            $data['total_row'] = count($result);
            $data['total_amount'] = $totalAmount;
            return $data;
        } else {
            return FALSE;
        }
    }

    public function getIncomeExpenseInfo($startDate = NULL, $endDate = NULL) {
        $this->db->select('*')->from('income_expense_list')
                ->where('DATE(created_date_time) >=', $startDate)
                ->where('DATE(created_date_time) <=', $endDate);
        $query = $this->db->get();
        $this->session->set_userdata("income_expense_query", $this->db->last_query());
        if ($query->num_rows() > 0) {
            $data = array();
            $result = array();
            $totalAmount = 0;
            $expenseAmount = 0;
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['payment_source'] = $row->payment_source;
                $result[$i]['description'] = $row->description;
                $result[$i]['status'] = $row->status;
                $result[$i]['amount'] = $row->amount;
                $result[$i]['system_id'] = $row->system_id;
                $totalAmount = $totalAmount + $row->amount;
                if ($row->status == 'expense') {
                    $expenseAmount = $expenseAmount + $row->amount;
                }
                $result[$i]['reference_id'] = $row->reference_id;
                $result[$i]['reference_remark'] = $row->reference_remark;
                $result[$i]['attatchment'] = $row->attatchment;
                $result[$i]['created_date_time'] = $row->created_date_time;
                $result[$i]['updated_date_time'] = $row->updated_date_time;
                $i++;
            }
            $data['result'] = $result;
            $data['total_row'] = count($result);
            $data['total_amount'] = $totalAmount;
            $data['total_expense'] = $expenseAmount;
            $data['total_income'] = $totalAmount - $expenseAmount;
            return $data;
        }
        return FALSE;
    }

    public function getTransactionInfo($startDate, $endDate, $gateway = NULL, $transcation_type = NULL, $transcation_status = NULL, $compare_amount = NULL, $compaire_final_amount = NULL) {
        $result = array();
        $data = array();
        $this->db->select('*')->from('subscription_order_list')
                ->where('DATE(order_created_date_time) >=', $startDate)
                ->where('DATE(order_created_date_time) <=', $endDate);
        if ($transcation_type != NULL) {
            $this->db->where('trx_type', $transcation_type);
        }
        if ($transcation_status != NULL) {
            $this->db->where('payment_status', $transcation_status);
        }
        if ($compare_amount != NULL) {
            $this->db->where("(total_receivable_amount $compare_amount)");
        }
        if ($gateway != NULL) {
            $payment_method_type_id = $this->getPaymentMethodTypeId($gateway);
            $this->db->where('payment_method_type_id', $payment_method_type_id);
        }
        if ($compaire_final_amount != NULL) {

            $this->db->where("((total_receivable_amount-gateway_fee) $compare_amount)");
        }

        $query = $this->db->get();

        $this->session->set_userdata("transaction_query", $this->db->last_query());
        if ($query->num_rows() > 0) {
            $totalAmount = 0;
            $totalFinalAmount = 0;
            $totalGatewayFee = 0;
            $totalTrx = 0;
            $totalRefund = 0;
            $totalChargeback = 0;
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['created_date'] = $row->order_created_date_time;
                $result[$i]['order_id'] = $row->reference_id;
                $result[$i]['gateway_id'] = $row->gateway_transaction_id;
                $result[$i]['trx_type'] = $row->trx_type;
                $result[$i]['trx_amount'] = $row->total_receivable_amount;
                $result[$i]['trx_status'] = $row->payment_status;
                $result[$i]['courier_fee'] = $courier_fee = $row->shipping_cost;

                $totalAmount = $totalAmount + $row->total_receivable_amount;

                $result[$i]['gateway_fee'] = $gatewayFee = $this->getGatewayFee($row->payment_method_type_id, $row->total_receivable_amount);
                $totalGatewayFee = $totalGatewayFee + $gatewayFee;

                $result[$i]['final_amount'] = $final_amount = $row->total_receivable_amount - ($gatewayFee + $courier_fee);
                $totalFinalAmount = $totalFinalAmount + $final_amount;

                $i++;
            }
            $data['result'] = $result;
            $data['total_row'] = count($result);
            $data['total_trx'] = count($result);
            $data['total_amount'] = $totalAmount;
            $data['total_fee'] = $totalGatewayFee;
            $data['total_final_amount'] = $totalFinalAmount;
            return $data;
        }
    }

    private function getPaymentMethodTypeId($name) {

        $sql = "SELECT id FROM subscription_payment_method_types WHERE name='$name'";
        $query1 = $this->db->query($sql);
        if ($query1->num_rows() > 0) {
            $payment_method_type_id = $query1->row()->id;
            return $payment_method_type_id;
        }
        return FALSE;
    }

    public function getGatewayFee($paymentMethodTypeID, $saleAmount) {
        $query = $this->db->get_where('subscription_payment_method_types', array('id' => $paymentMethodTypeID));
        if ($query->num_rows() > 0) {
            $gatewayName = $query->row()->name;
            $query2 = $this->db->get_where('subscription_gateway_info', array('gateway_name' => $gatewayName));
            if ($query2->num_rows() > 0) {
                if ($query2->row()->fee_type == 'percentage') {
                    $discountAmout = ($saleAmount * $query2->row()->gateway_fee) / 100;
                } else {
                    return $query2->row()->gateway_fee;
                }
            }
        }
        return FALSE;
    }

    public function geGatewayName() {
        $this->db->select('*')->from('subscription_gateway_info')->group_by('gateway_name');
        $query = $this->db->get();
        $response = array('' => 'Gateway - Any');
        foreach ($query->result() as $aData) {
            $response[$aData->gateway_name] = $aData->gateway_name;
        }
        return $response;
    }

    public function getPaymentMethod() {
        $this->db->select('*')->from('subscription_payment_method_types')->where('name != ','sign up');
        $query = $this->db->get();
        $response = array('' => 'Payment Source - Any');
        foreach ($query->result() as $aData) {
            $response[$aData->name] = $aData->name;
        }
        return $response;
    }

    public function geGatewayInfoById($id) {
        $query = $this->db->get_where('subscription_order_list', array('gateway_transaction_id' => $id));

        $result = array();

        if ($query->result()) {
            $query->row()->order_id = $query->row()->reference_id;
            $query->row()->product_name = $this->getProductNameByProductId($query->row()->product_id);
            $query->row()->payment_method_type_name = $this->getPaymentMethodByPaymentId($query->row()->payment_method_type_id);
            $query->row()->price = $query->row()->rate;
            return $query->row();
        }
        return FALSE;
    }

}
