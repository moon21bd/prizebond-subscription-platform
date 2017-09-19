<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-09-14
 */

class Api_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function doesMatchApiKey($api_key) {

        $this->db->select('api_key');
        $query = $this->db->get_where('app_information', array('api_key' => $api_key));
        if ($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function userAuthCheck($email, $password) {
        $this->db->select('id');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $query = $this->db->get_where('user', array('email' => $email, 'password' => md5($password)));
        } else {
            $query = $this->db->get_where('user', array('mobile_number' => $email, 'password' => md5($password)));
        }



        if ($query->result()) {
            return $query->row()->id;
        } else {
            return false;
        }
    }

    public function getAUserParchasedSummaryInfo($userId) {

        $query = $this->db->get_where('subscription_product_purchase_list', array('user_id' => $userId));
        if ($query->num_rows() > 0) {
            $advertisement = 'ON';
            $total_bond = 0;
            foreach ($query->result() as $row) {
                $vadidEndDate = date('Y-m-d', strtotime($row->valid_end_datetime));
                if ($vadidEndDate > date('Y-m-d')) {
                    $query = $this->db->get_where('subscription_product_list', array('id' => $row->product_id));
                    $attrValue = $this->getProductAttributeAndValue($row->product_id);
                    if (isset($attrValue['unit']) && !empty($attrValue['unit'])) {
                        if ($attrValue['unit'] == 'bond') {

                            $bonds = $this->getProductUnitValueByProductIdAndAppId($row->product_id);
                            $total_bond += (int) $bonds;
                        }
                    }
                    if (isset($attrValue['unit']) && !empty($attrValue['unit'])) {
                        if ($attrValue['unit'] == 'days') {
                            $advertisement = 'OFF';
                        }
                    }
                    if (!isset($attrValue['unit']) && !empty($attrValue['price'])) {
                        $advertisement = 'OFF';
                    }
                }
            }
            $final['advertisement'] = $advertisement;
            $final['total_user_purchased_prizebond'] = $total_bond;
            return $final;
        } else {
            return array();
        }
    }

    public function getVerifyCodeSentCounterForAUser($userId) {

        $query = $this->db->get_where('subscription_error_coupon_tracking', array('user_id' => trim($userId)));
        if ($query->result()) {
            $row = $query->row();
            $startDate = $row->create_date_time;
            //$endDate = $row->update_date_time;
            $endDate = date('Y-m-d H:i:s');
            $hourdiff = round((strtotime($endDate) - strtotime($startDate)) / 3600, 1);
            //if ($hourdiff > 24 && $row->counter == 3) {
            if ($hourdiff > 24) {
                $this->db->delete('subscription_error_coupon_tracking', array('id' => $row->id));
                $this->getErrorCouponInfo($userId);
            } else {
                return $query->row_array();
            }
        } else {
            $this->getErrorCouponInfo($userId);
        }
    }

    private function getErrorCouponInfo($userId) {

        $verifyCounterData = array(
            'user_id' => $userId,
            'counter' => 0,
            'create_date_time' => date('Y-m-d H:i:s')
        );
        $this->db->insert('subscription_error_coupon_tracking', $verifyCounterData);
        $query = $this->db->get_where('subscription_error_coupon_tracking', array('user_id' => trim($userId)));
        return $query->row_array();
    }

    public function updateInvalidCouponCounter($userId, $updateData = array()) {
        $this->db->update('subscription_error_coupon_tracking', $updateData, array('user_id' => $userId));
        if ($this->db->affected_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function checkInvalidCoupon($userId) {

        $query = $this->db->get_where('subscription_error_coupon_tracking', array('user_id' => $userId));
        if ($query->result()) {
            $row = $query->row();
            $startDate = $row->create_date_time;
            $endDate = $row->update_date_time;
            $hourdiff = round((strtotime($endDate) - strtotime($startDate)) / 3600, 1);
            if ($hourdiff > 24) {
                $this->db->delete('subscription_error_coupon_tracking', array('id' => $row->id));
                return TRUE;
            } else {
                if ($row->counter >= 3 && $hourdiff < 24) {
                    return FALSE;
                } else {
                    return TRUE;
                }
            }
        } else {
            return TRUE;
        }
    }

    public function checkVerifyCodeSendCounter($table, $where = array()) {

        $query = $this->db->get_where($table, $where);
        if ($query->result()) {
            $row = $query->row();
            $startDate = $row->create_date_time;
            $endDate = $row->update_date_time;
            $hourdiff = round((strtotime($endDate) - strtotime($startDate)) / 3600, 1);
            if ($hourdiff > 24) {
                $this->db->delete($table, array('id' => $row->id));
                return TRUE;
            } else {
                if ($row->counter >= 3 && $hourdiff < 24) {
                    return FALSE;
                } else {
                    return TRUE;
                }
            }
        } else {
            return TRUE;
        }
    }

    public function getUserPurchaseStatusByRedeemCoupon($where /* $productId, $parchaseId */) {
        $query = $this->db->get_where('subscription_product_purchase_list', $where);
        if ($query->num_rows() > 0) {
            $advertisement = 'ON';
            $i = 0;
            foreach ($query->result() as $row) {
                $bondInfo = array();
                $vadidEndDate = date('Y-m-d', strtotime($row->valid_end_datetime));
                if ($vadidEndDate > date('Y-m-d')) {
                    $query = $this->db->get_where('subscription_product_list', array('id' => $row->product_id));
                    $attrValue = $this->getProductAttributeAndValue($row->product_id);

                    if (isset($attrValue['unit']) && !empty($attrValue['unit'])) {
                        if ($attrValue['unit'] == 'bond') {
                            $bondInfo['product_name'] = $query->row()->name;
                            $bondInfo['total_prizebond'] = $attrValue['unit_value'];
                            $bondInfo['valid_end_date'] = $row->valid_end_datetime;
                            $i++;
                        }
                    }
                    if (isset($attrValue['unit']) && !empty($attrValue['unit'])) {
                        if ($attrValue['unit'] == 'days') {
                            $advertisement = 'OFF';
                        }
                    }
                    if (!isset($attrValue['unit']) && !empty($attrValue['price'])) {
                        $advertisement = 'OFF';
                    }
                }
            }
            $final['advertisement'] = $advertisement;
            $final['bond_info'] = $bondInfo;
            return $final;
        } else {
            return FALSE;
        }
    }

    public function getCouponSeriesIdByAppIdAndCouponSeries($couponSeries) {
        $query = $this->db->get_where('subscription_coupon_series', array('name' => $couponSeries));
        if ($query->result()) {
            return $query->row()->id;
        } else {
            return FALSE;
        }
    }

    public function getProductList($appId, $userId) {

        $this->db->group_by('product_category_id');
        $query = $this->db->get_where('subscription_product_purchase_list', array('app_list_id' => $appId, 'user_id' => $userId));
        if ($query->num_rows() > 0) {

            foreach ($query->result() as $row) {
                $categoryName = $this->getCategoryNameById($row->product_category_id);
                $result = array();
                $query = $this->db->get_where('product_list', array('product_category_id' => $row->product_category_id));

                $i = 0;
                foreach ($query->result() as $row) {
                    $result[$categoryName][$i]['name'] = $row->name;
                    $result[$categoryName][$i]['price'] = $row->price;
                    $i++;
                }

                $finalArray[] = $result;
            }

            return $finalArray;
        } else {
            return FALSE;
        }
    }

    public function getAUserSubscriptionInfo($userId) {
        $subscription = new stdClass();
        $query = $this->db->get_where('subscription_product_purchase_list', array('user_id' => $userId));

        if ($query->num_rows() > 0) {
            $advertisement = 'ON';
            $total_bond = 0;

            foreach ($query->result() as $row) {

                if ($row->purchased_by != 'sign up') {
                    $advertisement = 'OFF';
                }

                $vadidEndDate = date('Y-m-d', strtotime($row->valid_end_datetime));

                if ($vadidEndDate > date('Y-m-d')) {

                    $productInfo = $this->getProductInfoById($row->product_id);


                    if ($productInfo->attributes) {

                        if ($productInfo->attributes['unit'] == 'bond') {
                            $total_bond += (int) $productInfo->attributes['unit_value'];
                        } elseif ($productInfo->attributes['unit'] == 'days') {

                            $advertisement = 'OFF';
                        }
                    }
                }
            }

            $advertisement = 'OFF'; // insturcted by Boss on 27 Apr 2017 5.01pm
            $subscription->advertisement = $advertisement;
            $subscription->bond_max_capacity = $total_bond;
            $subscription->bond_added = $this->getTotalBondNumersOfAUser($userId);
            return $subscription;
        } else {
            return FALSE;
        }
    }

    public function getAllPaymentMethod() {
        $this->db->select('name');
        $query = $this->db->get('subscription_payment_method_types');
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $method[] = $row->name;
            }
            return $method;
        } else {
            return FALSE;
        }
    }

    private function getCategoryNameById($id) {

        $query = $this->db->get_where('subscription_product_categories', array('id' => $id));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    public function matchPurchaseList($where) {

        $query = $this->db->get_where('subscription_product_purchase_list', $where);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    public function checkCouponRedeemed($where) {

        $query = $this->db->get_where('subscription_product_purchase_list', $where);
        if ($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function doesExit($where) {

        $query = $this->db->get_where('subscription_product_purchase_list', $where);
        if ($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function doesExitOne($table, $where) {
        $this->db->select('id');
        $query = $this->db->get_where($table, $where);
        if ($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function getCouponPurchaseList($where) {

        $query = $this->db->get_where('subscription_product_purchase_list', $where);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    public function redeemCoupon($data) {

        $this->db->update('subscription_product_purchase_list', array('coupon_redeemed' => 'YES'), $data);
        $query = $this->db->get_where('subscription_product_purchase_list', array('app_list_id' => $data['app_list_id'], 'user_id' => $data['user_id']));
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    public function getSubscriptionList($appId) {

        $query = $this->db->get_where('subscription_product_list', array('app_list_id' => $appId));
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    public function matchApiKey($table, $where) {
        $query = $this->db->get_where($table, $where);
        if ($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function getUserPurchaseList($where) {
        $query = $this->db->get_where('subscription_product_purchase_list', $where);
        if ($query->num_rows() > 0) {
            $results = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $vadidEndDate = date('Y-m-d', strtotime($row->valid_end_datetime));
                if ($vadidEndDate > date('Y-m-d')) {
                    $query = $this->db->get_where('product_list', array('id' => $row->product_id));
                    $results[$i]['id'] = $query->row()->id;
                    $results[$i]['app_type'] = $this->getAppTypeByAppTypeId($query->row()->app_types_id);
                    $results[$i]['app_name'] = $this->getAppNameByAppNameId($query->row()->app_list_id);
                    $results[$i]['product_cat'] = $this->getProductCatByProductCatId($query->row()->product_category_id);
                    $results[$i]['product_type'] = $this->getProductTypeByProductTypeId($query->row()->product_type_id);
                    $results[$i]['product_name'] = $query->row()->name;
                    $results[$i]['price'] = $query->row()->price;
                    $results[$i]['product_value'] = $query->row()->product_value;
                    $results[$i]['unit'] = $query->row()->unit;
                    $results[$i]['purchased_by'] = $row->purchased_by;
                    $results[$i]['valid_start_datetime'] = $row->valid_start_datetime;
                    $results[$i]['valid_end_datetime'] = $row->valid_end_datetime;
                    $results[$i]['created_date_time'] = $query->row()->created;
                    $i++;
                }
            }

            return $results;
        } else {
            return FALSE;
        }
    }

    public function getProductAttributeAndValue($productId) {

        $query = $this->db->get_where('subscription_product_attribute_values', array('product_id' => $productId));
        if ($query->num_rows() > 0) {
            $data = array();
            foreach ($query->result() as $row) {
                $query1 = $this->db->get_where('subscription_products_attributes', array('id' => $row->attribute_id));
                if ($query1->num_rows() > 0) {
                    $atrr = strtolower($query1->row()->name);
                    if (strpos($atrr, ' ') != FALSE) {
                        $atrr = str_replace(' ', '_', $atrr);
                    }
                    $data["$atrr"] = $row->value;
                }
            }
            return $data;
        }return FALSE;
    }

    public function getAUserParchasedStatus($where) {
        $query = $this->db->get_where('subscription_product_purchase_list', $where);
        if ($query->num_rows() > 0) {
            $advertisement = 'ON';
            $finalInfo = array();
            $i = 1;
            $total_bond = 0;
            foreach ($query->result() as $row) {
                $bondInfo = array();
                $vadidEndDate = date('Y-m-d', strtotime($row->valid_end_datetime));
                if ($vadidEndDate > date('Y-m-d')) {

                    $query = $this->db->get_where('subscription_product_list', array('id' => $row->product_id));

                    $attrValue = $this->getProductAttributeAndValue($row->product_id, $where['app_list_id']);

                    if (isset($attrValue['unit']) && !empty($attrValue['unit'])) {
                        if ($attrValue['unit'] == 'bond') {

                            $bonds = $this->getProductUnitValueByProductIdAndAppId($row->product_id, $where['app_list_id']);
                            $total_bond += (int) $bonds;

                            $bondInfo['product_name'] = $query->row()->name;
                            $bondInfo['total_prizebond'] = $bonds;
                            $bondInfo['valid_end_date'] = $row->valid_end_datetime;
                            $finalInfo[] = $bondInfo;
                            $i++;
                        }
                    }

                    if (isset($attrValue['unit']) && !empty($attrValue['unit'])) {
                        if ($attrValue['unit'] == 'days') {
                            $advertisement = 'OFF';
                        }
                    }

                    if (!isset($attrValue['unit']) && !empty($attrValue['price'])) {
                        $advertisement = 'OFF';
                    }
                }
            }
            $final['advertisement'] = $advertisement;
            $final['total_prizebond'] = $total_bond;
            $final['bond_info'] = $finalInfo;
            return $final;
        } else {
            return FALSE;
        }
    }

    public function getProductUnitValueByProductIdAndAppId($productId) {
        $query = $this->db->get_where('subscription_product_attribute_values', array('product_id' => $productId));
        if ($query->num_rows() > 0) {
            return $query->row()->value;
        } else {
            return TRUE;
        }
    }

    public function getAppInitialStatus($where) {
        $query = $this->db->get_where('subscription_product_purchase_list', $where);
        if ($query->num_rows() > 0) {
            $status = array();
            $status['advertisement'] = 'ON';
            $status['total_prizebond_capacity'] = 25;
            return $status;
        } else {
            return FALSE;
        }
    }

    public function get_data($table, $where) {
        $query = $this->db->get_where($table, $where);
        if ($query->result()) {
            return $query->row_array();
        } else {
            return false;
        }
    }

    public function update($table, $data, $where) {
        $this->db->where($where);
        return $this->db->update($table, $data);
    }

    public function savePaymentInfo($table, $data) {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    public function save($table, $data) {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    public function insert($table, $data) {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    public function updateInfo($table, $data, $where) {
        $this->db->update($table, $data, $where);
        return TRUE;
    }

    public function checkExistingTrxID($txtId) {
        $query = $this->db->get_where('subscription_payment_bkash_queue', array('trxId' => $txtId, 'payment_status' => 'success'));
        if ($query->num_rows() == 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function getUserCouponFailedCount($userId) {

        $this->db->select('*')->from('subscription_error_coupon_tracking')->where('user_id', $userId);

        $query = $this->db->get();
        if ($query->num_rows() > 0) {

            return $query->row_array();
        } else {
            return FALSE;
        }
    }

    public function getOrderHistoryByUserId($limit, $offset, $userId) {

        $data = array();
        $offset = $offset ? $offset : 0;

        $sql = "SELECT 
                subscription_order_list.`reference_id`,
                subscription_order_list.`product_id`,
                subscription_order_list.`product_description`,
                subscription_order_list.`currency`,
                subscription_order_list.`quantity`,
                subscription_order_list.`rate`,
                subscription_order_list.`shipping_cost`,
                subscription_order_list.`discount`,
                subscription_order_list.`total_receivable_amount`,
                subscription_order_list.`gateway_transaction_id`,
                subscription_order_list.`payment_status`,
                subscription_order_list.`order_status`,
                subscription_order_list.`sandbox`,
                subscription_order_list.`shipping_address`,
                subscription_order_list.`order_created_date_time`,
                subscription_order_list.`payment_date_time`,
                user.user_id,subscription_payment_method_types.name AS payment_method_name,subscription_product_list.name AS product_name
                FROM subscription_order_list
                LEFT JOIN user ON user.id = subscription_order_list.user_id
                LEFT JOIN subscription_payment_method_types ON subscription_payment_method_types.id = subscription_order_list.payment_method_type_id
                LEFT JOIN subscription_product_list ON subscription_order_list.product_id = subscription_product_list.id
                WHERE subscription_order_list.user_id = '$userId'
                ORDER BY subscription_order_list.id DESC LIMIT $limit OFFSET $offset";

        $query = $this->db->query($sql);
        if ($query->result()) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function getGatewayFee($paymentMethodTypeID, $saleAmount) {
        $query = $this->db->get_where('subscription_payment_method_types', array('id' => $paymentMethodTypeID));
        if ($query->num_rows() > 0) {
            $gatewayName = $query->row()->name;
            $query2 = $this->db->get_where('subscription_gateway_info', array('gateway_name' => $gatewayName));
            if ($query->num_rows() > 0) {
                if ($query->row()->fee_type == 'percentage') {
                    //need to calculate
                    $discountAmout = ($saleAmount * $query->row()->gateway_fee) / 100;
                } else {
                    return $query->row()->gateway_fee;
                }
            }
        }
        return FALSE;
    }

    private function getAppTypeByAppTypeId($typeId) {
        $query = $this->db->get_where('subscription_app_types', array('id' => $typeId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    private function getProductCatByProductCatId($catId) {
        $query = $this->db->get_where('subscription_product_categories', array('id' => $catId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    private function getProductTypeByProductTypeId($proId) {
        $query = $this->db->get_where('subscription_product_types', array('id' => $proId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    /* From API model */
    public function getAUsersWinInfo($userId) {
        $winInfo = new stdClass();
        $query = $this->db->get_where('user_prizebond_won_list', array('user_id' => $userId));

        if ($query->num_rows() > 0) {

            return $query->result();
        } else {
            return FALSE;
        }
    }

    private function getProductInfoById($productId) {

        $query = $this->db->get_where('subscription_product_list', array('id' => $productId));

        if ($query->num_rows() > 0) {

            $data = new stdClass();
            $data->summary = $query->row();


            $query = $this->db->get_where('subscription_product_attribute_values', array('product_id' => $productId));
            if ($query->num_rows() > 0) {
                $attributes = array();
                foreach ($query->result() as $row) {
                    $query1 = $this->db->get_where('subscription_products_attributes', array('id' => $row->attribute_id));
                    if ($query1->num_rows() > 0) {
                        $atrr = strtolower($query1->row()->name);
                        if (strpos($atrr, ' ') != FALSE) {
                            $atrr = str_replace(' ', '_', $atrr);
                        }
                        $attributes["$atrr"] = $row->value;
                    }
                }
                $data->attributes = $attributes;
                return $data;
            }return FALSE;
        } else {
            return FALSE;
        }
    }

    private function getTotalBondNumersOfAUser($userId) {
        $query = $this->db->query('SELECT COUNT(id) AS total FROM user_prizebond_list WHERE user_id =' . $userId);
        return $query->row()->total;
    }

}
