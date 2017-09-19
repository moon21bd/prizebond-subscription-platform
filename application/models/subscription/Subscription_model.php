<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-09-19
 */

class Subscription_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function getSubscriptionList() {

        $this->db->order_by("ordering", "asc");
        $query = $this->db->get_where('subscription_product_list', array('status' => 1));
        if ($query->num_rows() > 0) {
            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['user_id'] = $this->getUserIdFromPurchaseList($query->row()->id);
                $result[$i]['app_type'] = $this->getAppTypeByAppTypeId($row->app_types_id);
                $result[$i]['product_cat'] = $this->getProductCatByProductCatId($row->product_category_id);
                $result[$i]['product_type'] = $this->getProductTypeByProductTypeId($row->product_type_id);
                $result[$i]['name'] = $row->name;
                $result[$i]['attributeName'] = $this->getProductAttributeValue($row->id);
                $i++;
            }
            return $result;
        } else {
            return FALSE;
        }
    }

    public function subscriptionListByUserId($userId) {
        $this->db->order_by("id", "DESC");
        $this->db->where(array('user_id' => $userId, 'status' => 1, 'valid_start_datetime !=' => '0000-00-00 00:00:00', 'valid_end_datetime !=' => '0000-00-00 00:00:00'));
        $query = $this->db->get('subscription_product_purchase_list');
        if ($query->num_rows() > 0) {
            $results = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $query1 = $this->db->get_where('subscription_product_list', array('id' => $row->product_id));
                $results[$i]['id'] = $query1->row()->id;
                $results[$i]['purchased_by'] = $row->purchased_by;
                $results[$i]['valid_start_datetime'] = $this->convertEnglishDateToBangla($row->valid_start_datetime);
                $results[$i]['valid_end_datetime'] = $this->convertEnglishDateToBangla($row->valid_end_datetime);
                $results[$i]['order_id'] = $row->order_reference_id;
                $results[$i]['offer_eligibility'] = 'no';
                $results[$i]['app_type'] = $this->getAppTypeByAppTypeId($query1->row()->app_types_id);
                $results[$i]['product_cat'] = $this->getProductCatByProductCatId($query1->row()->product_category_id);
                $results[$i]['product_type'] = $this->getProductTypeByProductTypeId($query1->row()->product_type_id);
                $results[$i]['name'] = $query1->row()->name;
                $attribue = $this->getProductAttributeValue($query1->row()->id);
                $results[$i]['attributeName'] = $attribue;
                $results[$i]['price'] = $this->covertAmountToBangla($attribue['price']);
                $results[$i]['created_date_time'] = $row->created_datetime;

                $orderInfo = $this->getOrderInfo(trim($row->order_reference_id));
                if ($orderInfo) {
                    $results[$i]['quantity'] = $orderInfo->quantity ? $orderInfo->quantity : 0;
                    $results[$i]['total_price'] = $this->covertAmountToBangla($orderInfo->total_receivable_amount);
                    $results[$i]['shipping_cost'] = $this->covertAmountToBangla($orderInfo->shipping_cost);
                    $results[$i]['discount'] = $this->covertAmountToBangla($orderInfo->discount);
                    $results[$i]['service_charge'] = $this->covertAmountToBangla($orderInfo->service_charge);
                    $results[$i]['gateway_fee'] = $this->covertAmountToBangla($orderInfo->gateway_fee);
                    $results[$i]['chargeback_fee'] = $this->covertAmountToBangla($orderInfo->chargeback_fee);
                    $results[$i]['refund_fee'] = $this->covertAmountToBangla($orderInfo->refund_fee);
                }
                $i++;
            }
            return $results;
        } else {
            return FALSE;
        }
    }

    public function prizebondSubscriptionListByUserId($userId) {
        $this->db->order_by("id", "DESC");
        $this->db->where(array('user_id' => $userId, 'status' => 1, 'valid_start_datetime' => '0000-00-00 00:00:00', 'valid_end_datetime' => '0000-00-00 00:00:00'));
        $query = $this->db->get('subscription_product_purchase_list');
        if ($query->num_rows() > 0) {
            $results = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $query1 = $this->db->get_where('subscription_product_list', array('id' => $row->product_id));
                $results[$i]['id'] = $query1->row()->id;
                $results[$i]['order_id'] = $row->order_reference_id;


                $orderInfo = $this->getOrderInfo($row->order_reference_id);

                $results[$i]['quantity'] = $orderInfo->quantity ? $orderInfo->quantity : 0;
                $results[$i]['offer_eligibility'] = 'no';
                $results[$i]['app_type'] = $this->getAppTypeByAppTypeId($query1->row()->app_types_id);
                $results[$i]['product_cat'] = $this->getProductCatByProductCatId($query1->row()->product_category_id);
                $results[$i]['product_type'] = $this->getProductTypeByProductTypeId($query1->row()->product_type_id);
                $results[$i]['name'] = $query1->row()->name;
                $attribue = $this->getProductAttributeValue($query1->row()->id);
                $results[$i]['attributeName'] = $attribue;
                $results[$i]['price'] = $this->covertAmountToBangla($attribue['price']);
                $results[$i]['purchased_by'] = $row->purchased_by;
                $results[$i]['order_date'] = $this->convertEnglishDateToBangla($row->created_datetime);
                $results[$i]['created_date_time'] = $row->created_datetime;

                if ($orderInfo) {
                    $results[$i]['total_price'] = $this->covertAmountToBangla($orderInfo->total_receivable_amount);
                    $results[$i]['shipping_cost'] = $this->covertAmountToBangla($orderInfo->shipping_cost);
                    $results[$i]['discount'] = $this->covertAmountToBangla($orderInfo->discount);
                    $results[$i]['service_charge'] = $this->covertAmountToBangla($orderInfo->service_charge);
                    $results[$i]['gateway_fee'] = $this->covertAmountToBangla($orderInfo->gateway_fee);
                    $results[$i]['chargeback_fee'] = $this->covertAmountToBangla($orderInfo->chargeback_fee);
                    $results[$i]['refund_fee'] = $this->covertAmountToBangla($orderInfo->refund_fee);
                }

                $i++;
            }
            return $results;
        } else {
            return FALSE;
        }
    }

    public function covertAmountToBangla($amount) {

        $amountArray = str_split($amount);
        $string = '';
        foreach ($amountArray as $number) {
            $string .= $this->convertStringToBangla($number);
        }
        return $string;
    }

    private function convertEnglishDateToBangla($param) {
        //$param = date("l F j Y a g:i", strtotime($param));
        $param = date("m/d/Y l g:i a", strtotime($param));
        $string = '';
        /* $dataArray = explode(' ', $param);
          $wekday = $dataArray[0];
          $month = $dataArray[1];
          $day = $dataArray[2];
          $year = $dataArray[3];
          $ampm = $dataArray[4];
          $extra = explode(':', $dataArray[5]);
          $hour = $extra[0];
          $minute = $extra[1];
         * *
         */

        //10/14/2016 Friday 3:08 pm
        $dataArray = explode(' ', $param);
        $dataArray1 = explode('/', $dataArray[0]);

        $wekday = $dataArray[1];

        $month = $dataArray1[0];
        $day = $dataArray1[1];
        $year = $dataArray1[2];

        $ampm = $dataArray[3];
        $extra = explode(':', $dataArray[2]);
        $hour = $extra[0];
        $minute = $extra[1];

        $banglaWeak = $this->convertStringToBangla($wekday);

        $string = '';
        $dayArray = str_split($day);
        foreach ($dayArray as $day) {
            $string .= $this->convertStringToBangla($day);
        }
        $banglaDay = $string;

        $string = '';
        $monthArray = str_split($month);
        foreach ($monthArray as $day) {
            $string .= $this->convertStringToBangla($day);
        }
        $banglaMonth = $string;

        //$banglaMonth = $this->convertStringToBangla($month);

        $yearArray = str_split($year);
        $string = '';
        foreach ($yearArray as $number) {
            $string .= $this->convertStringToBangla($number);
        }
        $banglaYear = $string;

        $banglaampm = $this->convertStringToBangla($ampm);

        $hourArray = str_split($hour);
        $string = '';
        foreach ($hourArray as $number) {
            $string .= $this->convertStringToBangla($number);
        }

        $banglaHour = $string;

        $minuteArray = str_split($minute);
        $string = '';
        foreach ($minuteArray as $number) {
            $string .= $this->convertStringToBangla($number);
        }

        $banglaMinute = $string;

        //return $banglaWeak . ", " . $banglaMonth . " " . $banglaDay . ", " . $banglaYear . " " . $banglaampm . ' ' . $banglaHour . '.' . $banglaMinute;
        // return $banglaMonth . '/' . $banglaDay . '/' . $banglaYear . ' ' . $banglaWeak . ' ' . $banglaHour . ':' . $banglaMinute . ' ' . $banglaampm;
        return $banglaMonth . '/' . $banglaDay . '/' . $banglaYear;
    }

    public function convertStringToBangla($param) {
        $param = trim($param);
        if ($param == "January") {
            return 'জানুয়ারী';
        } elseif ($param == "February") {
            return 'ফেব্রুয়ারি';
        } elseif ($param == "March") {
            return 'মার্চ';
        } elseif ($param == "April") {
            return 'এপ্রিল';
        } elseif ($param == "May") {
            return 'মে';
        } elseif ($param == "June") {
            return 'জুন';
        } elseif ($param == "July") {
            return 'জুলাই';
        } elseif ($param == "August") {
            return 'অগাস্ট';
        } elseif ($param == "September") {
            return 'সেপ্টেম্বর';
        } elseif ($param == "October") {
            return 'অক্টোবর';
        } elseif ($param == "November") {
            return 'নভেম্বর';
        } elseif ($param == "December") {
            return 'ডিসেম্বর';
        } elseif ($param == "0") {
            return "০";
        } elseif ($param == "1") {
            return "১";
        } elseif ($param == "2") {
            return "২";
        } elseif ($param == "3") {
            return "৩";
        } elseif ($param == "4") {
            return "৪";
        } elseif ($param == "5") {
            return "৫";
        } elseif ($param == "6") {
            return "৬";
        } elseif ($param == "7") {
            return "৭";
        } elseif ($param == "8") {
            return "৮";
        } elseif ($param == "9") {
            return "৯";
        } elseif ($param == "pm") {
            return "অপরাহ্ন";
        } elseif ($param == "am") {
            return "পূর্বাহ্ন";
        } elseif ($param == 'Saturday') {
            return 'শনিবার';
        } elseif ($param == 'Sunday') {
            return 'রবিবার';
        } elseif ($param == 'Monday') {
            return 'সোমবার';
        } elseif ($param == 'Tuesday') {
            return 'মঙ্গলবার';
        } elseif ($param == 'Wednesday') {
            return 'বুধবার';
        } elseif ($param == 'Thursday') {
            return 'বৃহস্পতিবার';
        } elseif ($param == 'Friday') {
            return 'শুক্রবার';
        }

        return $param;
    }

    public function couponListByUserId($userId) {
        $query = $this->db->get_where('subscription_coupon_redeemed_history', array('user_id' => $userId));
        if ($query->num_rows() > 0) {
            $results = array();
            foreach ($query->result() as $row) {
                $data['product_name'] = $this->getProductNameByProductTypeId($row->product_id);
                $data['coupon_code'] = $row->coupon_code;
                $data['created_date_time'] = $this->convertEnglishDateToBangla($row->created_date_time);
                $results[] = $data;
            }
            return $results;
        } else {
            return FALSE;
        }
    }

    private function getProductNameByProductTypeId($productId) {
        $query = $this->db->get_where('subscription_product_list', array('id' => $productId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    public function getProductInfoByProductId($productId) {
        $query = $this->db->get_where('subscription_product_list', array('id' => $productId));
        if ($query->num_rows() > 0) {
            $result['id'] = $query->row()->id;
            $result['product_id'] = $query->row()->id;
            $result['app_type'] = $this->getAppTypeByAppTypeId($query->row()->app_types_id);
            $result['product_cat'] = $this->getProductCatByProductCatId($query->row()->product_category_id);
            $result['product_type'] = $this->getProductTypeByProductTypeId($query->row()->product_type_id);
            $result['product_name'] = $query->row()->name;
            $attribute = $this->getProductAttributeValue($query->row()->id);


            if (isset($attribute['unit Value'])) {
                $result['unit_value'] = $attribute['unit Value'];
            }
            if (isset($attribute['unit'])) {
                $result['unit'] = $attribute['unit'];
            }
            if (isset($attribute['price'])) {
                $result['price'] = $attribute['price'];
                $result['price_bangla'] = $this->covertAmountToBangla($attribute['price']);
            }
            return $result;
        } else {
            return FALSE;
        }
    }

    public function getProductAttributeValue($productId) {
        $query = $this->db->get_where('subscription_product_attribute_values', array('product_id' => $productId));
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $query = $this->db->get_where('subscription_products_attributes', array('id' => $row->attribute_id));
                $attr = lcfirst($query->row()->name);
                $result[$attr] = $row->value;
            }
            return $result;
        } else {
            return FALSE;
        }
    }

    public function showPaymentData($appId) {
        $query = $this->db->get_where('subscription_product_list', array('app_list_id' => $appId));
        if ($query->num_rows() > 0) {
            $result = array();
            $result['id'] = $query->row()->id;
            $result['name'] = $query->row()->name;
            $result['price'] = $query->row()->price;
            $result['user_id'] = $this->getUserIdFromPurchaseList($query->row()->id);
            $result['app_id'] = $this->getAppIdByAppNameId($query->row()->id);
            $result['app_name'] = $this->getAppNameByAppNameId($query->row()->id);
            return $result;
        } else {
            return FALSE;
        }
    }

    public function viewProductPurchase($id) {
        $query = $this->db->get_where('subscription_product_purchase_list', array('id' => $id));
        $result = array();
        $result['id'] = $query->row()->id;
        $result['user_id'] = $query->row()->user_id;
        $result['app_type'] = $this->getAppTypeByAppTypeId($query->row()->app_types_id);
        $result['product_cat'] = $this->getProductCatByProductCatId($query->row()->product_category_id);
        $result['product_type'] = $this->getProductTypeByProductTypeId($query->row()->product_type_id);
        $result['product_name'] = $this->getProListByProListId($query->row()->product_id);
        $result['coupon_code'] = $query->row()->coupon_code;
        $result['start_datetime'] = $query->row()->valid_start_datetime;
        $result['end_datetime'] = $query->row()->valid_end_datetime;
        return $result;
    }

    public function viewProductList($appId) {
        $query = $this->db->get_where('subscription_product_list', array('app_list_id' => $appId));
        if ($query->num_rows() > 0) {
            $result = array();
            $result['id'] = $query->row()->id;
            $result['app_type'] = $this->getAppTypeByAppTypeId($query->row()->app_types_id);
            $result['app_name'] = $this->getAppNameByAppNameId($query->row()->app_list_id);
            $result['product_cat'] = $this->getProductCatByProductCatId($query->row()->product_category_id);
            $result['product_type'] = $this->getProductTypeByProductTypeId($query->row()->product_type_id);
            $result['name'] = $query->row()->name;
            $result['price'] = $query->row()->price;
            return $result;
        } else {
            return FALSE;
        }
    }

    //........... show data........
    public function getProductList() {
        $this->db->select('*')->from('subscription_product_list');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $result = array();
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['app_type'] = $this->getAppTypeByAppTypeId($row->app_types_id);
                $result[$i]['app_name'] = $this->getAppNameByAppNameId($row->app_list_id);
                $result[$i]['product_cat'] = $this->getProductCatByProductCatId($row->product_category_id);
                $result[$i]['product_type'] = $this->getProductTypeByProductTypeId($row->product_type_id);
                $result[$i]['name'] = $row->name;
                $result[$i]['price'] = $row->price;
            }

            return $result;
        } else {
            return FALSE;
        }
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

    private function getProListByProListId($proListId) {

        $query = $this->db->get_where('subscription_product_list', array('id' => $proListId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    private function getUserIdFromPurchaseList($proPurchaseId) {
        $query = $this->db->get_where('subscription_product_purchase_list', array('id' => $proPurchaseId));
        if ($query->num_rows() > 0) {
            return $query->row()->user_id;
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

    public function insert($table, $data) {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    public function update($table, $data, $where) {

        $this->db->update($table, $data, $where);
        return TRUE;
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

    public function getPaymentMethodInfo($paymentMethod) {
        $query = $this->db->get_where('subscription_payment_method_types', array('name' => $paymentMethod));
        if ($query->num_rows() > 0) {
            return $query->row();
        }
    }

    public function getOrderInfo($orderId) {
        $query = $this->db->get_where('subscription_order_list', array('reference_id' => $orderId));
        if ($query->num_rows() > 0) {
            return $query->row();
        }
    }

}
