<?php

defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('userProfileInfoPanel')) {

    function userProfileInfoPanel($users_personal_info) {
        // get the CI instanse
        $CI = &get_instance();
        $html = '';

        $verifyCounter = 0;
        $resetDate = NULL;
        $CI->db->select('*')->from('verify_counter')->where('user_id', $users_personal_info->id);
        $query2 = $CI->db->get();
        $userCounterInfo = $query2->row();

        if (!empty($userCounterInfo)) {

            $verifyCounter = $userCounterInfo->counter;
            $resetDate = date('d.m.y, g:i a', strtotime($userCounterInfo->create_date_time . ' +1 day'));
        }

        if (!empty($users_personal_info) && (is_object($users_personal_info))) {
            $active_products = ($users_personal_info->total_purchased_product) - ($users_personal_info->total_expired_product ? $users_personal_info->total_expired_product : 0);
            $users_personal_info->total_expired_product = $users_personal_info->total_expired_product ? $users_personal_info->total_expired_product : 0;
            $users_personal_info->total_purchased_amount = $users_personal_info->total_purchased_amount ? $users_personal_info->total_purchased_amount : 0;
            $users_personal_info->email = $users_personal_info->email ? $users_personal_info->email : "N/A";
            $users_personal_info->mobile_number = $users_personal_info->mobile_number ? $users_personal_info->mobile_number : 'N/A';
            $users_personal_info->no_of_associated_device = $users_personal_info->no_of_associated_device ? $users_personal_info->no_of_associated_device : 'N/A';
            $users_personal_info->created_date_time = $users_personal_info->created_date_time ? date('d.m.y, g:i a', strtotime($users_personal_info->created_date_time)) : 'N/A';
            $users_personal_info->updated_date_time = $users_personal_info->updated_date_time ? date('d.m.y, g:i a', strtotime($users_personal_info->updated_date_time)) : 'N/A';
            $users_personal_info->last_login_from = $users_personal_info->last_login_from ? $users_personal_info->last_login_from : 'N/A';
            $users_personal_info->allready_added_prizebond = $users_personal_info->allready_added_prizebond ? $users_personal_info->allready_added_prizebond : 0;
            $users_personal_info->rang_of_add_prizebond = $users_personal_info->rang_of_add_prizebond ? $users_personal_info->rang_of_add_prizebond : 0;
            $users_personal_info->overload = $users_personal_info->overload ? $users_personal_info->overload : 0;
            $active_products = ($users_personal_info->total_purchased_product) - ($users_personal_info->total_expired_product ? $users_personal_info->total_expired_product : 0);
            $users_personal_info->total_purchased_amount = $users_personal_info->total_purchased_amount ? $users_personal_info->total_purchased_amount : 0;
            $users_personal_info->total_expired_product = $users_personal_info->total_expired_product ? $users_personal_info->total_expired_product : 0;

            $html .= '<div class="panel panel-info" style="margin-top:40px;">
                <div class="panel-heading">User Profile</div>
                <div class="panel-body table-bg" style="padding:0;">
                    <div class="user-profile-top">
                        <div class="table-responsive">
                            <table class="table table-wrap">
                                <tbody>
                                    <tr>
                                        <td width="5%"> 
                                            <span class="user-photo"><a href="#"><img src="' . getPhoto($users_personal_info->image, 'images/') . '" alt="" title=""></a></span>
                                        </td>
                                        <td width="20%">
                                            <div class="user-photo-info">
                                                <a href="' . site_url('admin/users/profile/userId/' . $users_personal_info->id) . '" data-toggle="tooltip" data-html="true" data-placement="top" data-original-title="Internal ID : ' . $users_personal_info->id . '</br> External ID : ' . $users_personal_info->user_id . '"><strong>' . $users_personal_info->name . '</strong></a>
                                                <p class="user-info">';

            if ($users_personal_info->email_verify_status == 'YES') {
                $html .= '<i class="fa fa-envelope" aria-hidden="true"></i> ' . $users_personal_info->email . ' <i class="fa fa-check-circle text-success" aria-hidden="true"></i><br/>';
            } else {
                $html .= '<i class="fa fa-envelope" aria-hidden="true"></i> ' . $users_personal_info->email . ' <i class="fa fa-check-circle" aria-hidden="true"></i><br/>';
            }
            if ($users_personal_info->verify_status == 'YES') {
                $html .= '<i class="fa fa-phone" aria-hidden="true"></i> ' . $users_personal_info->mobile_number . ' <i class="fa fa-check-circle text-success" aria-hidden="true"></i><br/>';
            } else {

                $html .= '<i class="fa fa-phone" aria-hidden="true"></i>' . $users_personal_info->mobile_number . ' <i class="fa fa-check-circle" aria-hidden="true"></i><br/>';
            }
            $html .= '<i class="fa fa-arrow-right" aria-hidden="true"></i> V.Counter : ' . $verifyCounter . '<br/>';
            $html .= '<i class="fa fa-retweet" aria-hidden="true"></i> Reset : ' . $resetDate . '<br/>';
            $html .= '</div>';
            $html .= '</td>
                                        <td width="20%">
                                            <div class="user-photo-info">
                                                <strong>Activity Info</strong>
                                                <p class="user-info">';
            if (strpos($users_personal_info->last_login_from, 'Web') !== false) {
                $html .= '<i class="fa fa-laptop" aria-hidden="true"></i>  Device: ' . $users_personal_info->no_of_associated_device . '</br>';
            } else {
                $html .= '<i class="fa fa-tablet" aria-hidden="true"></i>  Device: ' . $users_personal_info->no_of_associated_device . '</br>';
            }


            $html .= '<i class="fa fa-calendar" aria-hidden="true"></i>Reg. Date: ' . $users_personal_info->created_date_time . '</br>';

            $html .= '<i class="fa fa-calendar" aria-hidden="true"></i> Last Login: ' . $users_personal_info->updated_date_time . '(' . $users_personal_info->last_login_from . ')';
            $html .= '</p> 
                                            </div>
                                        </td>
                                        <td width="20%">
                                            <div class="user-photo-info">
                                                <strong>Bond Info</strong>
                                                <p class="user-info">
                                                    <i class="fa fa-plus-square-o" aria-hidden="true"></i> Added : ' . $users_personal_info->allready_added_prizebond . '</br>
                                                    <i class="fa fa-shopping-basket" aria-hidden="true"></i> Current Capacity : ' . $users_personal_info->rang_of_add_prizebond . '</br>
                                                    <i class="fa fa-balance-scale" aria-hidden="true"></i> OverLoad : ' . $users_personal_info->overload . '</br>
                                                </p>
                                            </div>
                                        </td>
                                        <td width="20%">
                                            <div class="user-photo-info">
                                                <strong>Subscription Info</strong>
                                               
                                                <p class="user-info"><i class="fa fa-money" aria-hidden="true"></i> Purchased: ' . $users_personal_info->total_purchased_amount . '(BDT)<br/>
                                                    <i class="fa fa-shopping-cart" aria-hidden="true"></i> No. of Active Products : ' . $active_products . '</br>
                                                    <i class="fa fa-shopping-cart" aria-hidden="true"></i> No. of Expired Products : ' . $users_personal_info->total_expired_product . '</br>

                                                </p>
                                            </div>
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </div> 
                    </div>
                </div>
            </div>';
        }

        return $html;
    }

}


if (!function_exists('miniUserProfile')) {

    function miniUserProfile($userId, $image = TRUE) {
        // get the CI instanse
        $CI = &get_instance();
        $html = '';

        $CI->db->select('*')->from('user')->where('id', $userId);
        $query = $CI->db->get();
        $single_user = $query->row_array();

        if (!empty($single_user) && (is_array($single_user))) {
            $single_user['name'] = ($single_user['name']) ? ucfirst($single_user['name']) : 'N/A';
            $single_user['email'] = $single_user['email'] ? $single_user['email'] : 'N/A';
            $single_user['mobile_number'] = $single_user['mobile_number'] ? $single_user['mobile_number'] : 'N/A';

            $parameter = 'userId';

            $html .= '<td class="text-center" style="text-align: left">
                  <div class="pull-left">';
            if ($image != FALSE) {
                $html .= '<div class="auth_pic">
                    <a href="' . site_url('admin/users/profile/' . $parameter . '/' . $single_user['id']) . '"><img style="width: 60px; height:60px;" src="' . getPhoto($single_user['image'], 'images/') . '" alt="" class="img-thumbnail uth_profile_img donner-profile-thumb img-responsive"></a>
                   </div>';
            }
            $html .= '</div>
               <div class="pull-left">
                <div class="auth_info">
                    <span class="auth_info_name"><a href="' . site_url('admin/users/profile/' . $parameter . '/' . $single_user['id']) . '">' . ucfirst($single_user['name']) . '</a>';

            if ($single_user['user_type_id'] == 2) {
                $html .= ' (Dev.)';
            }
            $html .= '</span>';

            if ($single_user['email_verify_status'] == 'YES') {

                $html .= '<span class="auth_info_contact_info">' . $single_user['email'] . ' <i class="fa fa-check-circle text-success" aria-hidden="true"></i></span>';
            } else {

                $html .= '<span class="auth_info_contact_info">' . $single_user['email'] . ' <i class="fa fa-check-circle" aria-hidden="true"></i></span>';
            }

            if ($single_user['verify_status'] == 'YES') {

                $html .= '<span class="auth_info_contact_info">' . $single_user['mobile_number'] . ' <i class="fa fa-check-circle text-success" aria-hidden="true"></i></span>';
            } else {
                $html .= '<span class="auth_info_contact_info">' . $single_user['mobile_number'] . ' <i class="fa fa-check-circle" aria-hidden="true"></i></span>';
            }
            $html .= '</div>
                  </div>
                  <div class="clearfix"></div>
                  </td>';
        } else {
            $html .= '<td class="text-center" style="text-align: left">N/A</td>';
        }
        return $html;
    }

}

if (!function_exists('deviceInfo')) {

    function deviceInfo($deviceInfoId) {
        // get the CI instanse
        $CI = &get_instance();
        $html = '';

        $CI->db->select('*')->from('device_info')->where('id', $deviceInfoId);
        $query = $CI->db->get();
        $devicesArray = $query->row_array();

        if (!empty($devicesArray) && (is_array($devicesArray))) {


            $device_uuid = $devicesArray['device_uuid'] ? $devicesArray['device_uuid'] : 'N/A';
            $device_height = $devicesArray['device_height'] ? $devicesArray['device_height'] : 'N/A';
            $device_width = $devicesArray['device_width'] ? $devicesArray['device_width'] : 'N/A';
            $device_manufacturer = $devicesArray['device_manufacturer'] ? $devicesArray['device_manufacturer'] : 'N/A';
            $device_product = $devicesArray['device_product'] ? $devicesArray['device_product'] : 'N/A';
            $device_os_version = $devicesArray['device_os_version'] ? $devicesArray['device_os_version'] : 'N/A';
            $device_api_version = $devicesArray['device_api_version'] ? $devicesArray['device_api_version'] : 'N/A';
            $device_brand = $devicesArray['device_brand'] ? $devicesArray['device_brand'] : '';
            $device_model = $devicesArray['device_model'] ? $devicesArray['device_model'] : 'N/A';
            $device_status = $devicesArray['device_status'] ? $devicesArray['device_status'] : 'N/A';

            $html .= '<td class="text-left"><b>UUID : </b>' . $device_uuid . '<br/>';
            if (!empty($device_brand)) {
                $html .= '<a href="" data-toggle="tooltip" data-html="true" data-placement="top" data-original-title="UUID : ' . $device_uuid . ' </br> Screen :  ' . $device_height . ' X ' . $device_width . ' <br/>Manufacturar : ' . $device_manufacturer . ' <br/>Product : ' . $device_product . ' <br/>OS Version : ' . $device_os_version . ' <br/>Api Version : ' . $device_api_version . '">
                          <span style="text-transform:uppercase;">' . $device_brand . '</span></a>
                          (' . $device_model . ')<br/>';
            }


            if ($device_status == 'active') {
                $html .= '<span class="text-success">' . $device_status . '</span>';
            } else {
                $html .= '<span class = "failure">' . $device_status . '</span>';
            }

            $html .= '</td>';
        } else {
            $html .= '<td class="text-left" style="text-align: left">N/A</td>';
        }
        return $html;
    }

}