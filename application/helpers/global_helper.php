<?php

defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('check_login')) {

    function check_login() {

        $CI = &get_instance();
        if ($CI->session->id && $CI->session->name) {
            //load the model
            $CI->load->model('auth_model');
            $admin_login = $CI->auth_model->admin_login(array('id' => $CI->session->id, 'name' => $CI->session->name));
            if ($admin_login) {
                $login_data = array(
                    'id' => $admin_login->id,
                    'name' => $admin_login->name,
                    'email' => $admin_login->email,
                    'password' => $admin_login->password,
                    'logged_in' => TRUE
                );

                $CI->session->set_userdata($login_data);
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

}

if (!function_exists('get_data')) {

    function get_data($table, $where) {
        $CI = & get_instance();
        $CI->load->model('admin_model');
        return $CI->admin_model->get_data($table, $where);
    }

}

function isPerchased($id) {
    // get the CI instanse
    $CI = &get_instance();
    //Get perchased  
    $CI->db->select('*')->from('subscription_product_purchase_list')->where('product_id', $id);
    $query = $CI->db->get();
    if ($query->num_rows() > 0) {
        return TRUE;
    } else {
        return FALSE;
    }
}

//...  pagination ............//

if (!function_exists('generatePagging')) {

    function generatePagging($page_url, $total_rows, $per_page, $uri_segment = 3, $num_links = 2) {
        $CI = &get_instance();
        //load the pagging library
        $CI->load->library('pagination');
        // set the configuration
        //echo $config['base_url'] = site_url() . $page_url; 
        $config["base_url"] = base_url() . $page_url;
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['uri_segment'] = $uri_segment;
        $config['num_links'] = $num_links;
        // for search pagination link
        $config['reuse_query_string'] = TRUE;
        // pagging design section
        // open full tag
        $config['full_tag_open'] = '<ul class="pagination">';
        // close paggination
        $config['full_tag_close'] = '</ul>';

        // privious tag
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        //privious link
        $config['prev_link'] = '&lsaquo; Previous';

        // first tag
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        // first link
        $config['first_link'] = '&laquo; First';

        // last tag
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        // last link
        $config['last_link'] = 'Last &raquo;';

        // next tag
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        // next link
        $config['next_link'] = 'Next &rsaquo;';


        // current  link
        $config['cur_tag_open'] = '<li class="active"><a href="javascript:void(0)">';
        $config['cur_tag_close'] = '</a></li>';

        // number link
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';

        $CI->pagination->initialize($config);
    }

}

// show default image...........
if (!function_exists('getPhoto')) {

    function getPhoto($filename, $path) {
        if (!empty($filename)) {
            if (!file_exists($path . $filename))
                return base_url($path . 'avatar.jpeg');
            else
                return base_url($path . $filename);
        } else
            return base_url($path . 'avatar.jpeg');
    }

}

// get the blood group
if (!function_exists('get_prize_position')) {

    function get_prize_position($position) {
        $prizePosition = '';
        if ($position == '1st') {
            $prizePosition = 'first_position';
        } elseif ($position == '2nd') {
            $prizePosition = 'second_position';
        } elseif ($position == '3rd') {
            $prizePosition = 'third_position';
        } elseif ($position == '4th') {
            $prizePosition = 'fourth_position';
        } elseif ($position == '5th') {
            $prizePosition = 'fifth_position';
        }

        return $prizePosition;
    }

}


if (!function_exists('showUserName')) {

    function showUserName($id) {
        // get the CI instanse
        $CI = &get_instance();
        $CI->db->select('name')
                ->from('user')
                ->where('id', $id);
        $query = $CI->db->get();
        if ($query->num_rows() > 0) {
            $result = $query->row();
            return $result->name;
        } else {
            return '';
        }
    }

}

if (!function_exists('getUserPhotoName')) {

    function getUserPhotoName($id) {
        // get the CI instanse
        $CI = &get_instance();
        //Get image
        $CI->db->select('image')
                ->from('user')
                ->where('id', $id);
        $query = $CI->db->get();
        if ($query->num_rows() > 0) {
            $result = $query->row();
            return $result->image;
        } else {
            return '';
        }
    }

}
if (!function_exists('getUserMobileNumber')) {

    function getUserMobileNumber($id) {
        // get the CI instanse
        $CI = &get_instance();
        //Get image
        $CI->db->select('mobile_number')
                ->from('user')
                ->where('id', $id);
        $query = $CI->db->get();
        if ($query->num_rows() > 0) {
            $result = $query->row();
            return $result->mobile_number;
        } else {
            return '';
        }
    }

}
if (!function_exists('getUserEmail')) {

    function getUserEmail($id) {
        // get the CI instanse
        $CI = &get_instance();
        //Get image
        $CI->db->select('email')
                ->from('user')
                ->where('id', $id);
        $query = $CI->db->get();
        if ($query->num_rows() > 0) {
            $result = $query->row();
            return $result->email;
        } else {
            return '';
        }
    }

}

if (!function_exists('getMessageSubject')) {

    function getMessageSubject($id) {
        // get the CI instanse
        $CI = &get_instance();
        $CI->db->select('subject')
                ->from('user_support_messages')
                ->where('message_id', $id);
        $query = $CI->db->get();
        if ($query->num_rows() > 0) {
            $result = $query->row();
            return $result->subject;
        } else {
            return '';
        }
    }

}

if (!function_exists('CODActionsHelper')) {

    function CODActionsHelper($orderInfo) {
        // get the CI instanse
        $CI = &get_instance();

        $couponCode = '';

        if ($orderInfo['order_status'] == 'pending' || ($orderInfo['order_status'] == 'waiting_for_confirmed') || ($orderInfo['order_status'] == 'confirmed') || ($orderInfo['order_status'] == 'cancel')) {

            $couponCode = '';
        } else {
            $query = $CI->db->query("SELECT * FROM subscription_coupons WHERE order_id='" . $orderInfo['order_id'] . "'");
            $couponInfo = $query->row();

            $query = $CI->db->query("SELECT * FROM subscription_coupon_series WHERE id='" . $couponInfo->series_id . "'");

            if ($query->num_rows() > 0) {
                $seriesName = $query->row()->name;
            } else {
                $seriesName = 'N/A';
            }
            $couponCode = $seriesName . ' - ' . $couponInfo->coupon . ' : ' . $couponInfo->status;
        }

        $html = '';

        if ($orderInfo['payment_method_type_id'] == 7) {
            //coupon_generated, 'pending','failed','completed','confirmed','shipped','delivered','wrong_shipping_address','delivery_failed','waiting_for_confirmed','cancel'

            $defaultLabel = 'Unknown';
            $CODActionArray = array();
            if ($orderInfo['order_status'] == 'waiting_for_confirmed') { // call customer and confirm order
                $defaultLabel = 'Waiting';
                $CODActionArray = array('Confirmed', 'Cancel');
            } elseif ($orderInfo['order_status'] == 'cancel') { // call customer and confirm order
                $defaultLabel = 'Cancel';
                // $CODActionArray = array('Confirmed', 'Cancel');
            } elseif ($orderInfo['order_status'] == 'confirmed') {
                $defaultLabel = 'Confirmed';
                $orderId = $orderInfo['id'];
                $html .= '<a data-toggle="modal" href="' . base_url('/subscription/orders/couponFormForCOD/' . $orderId) . '" title="Add Coupon" data-target="#modalForAddCoupon"><button class="btn btn-info" type="button">Add Coupon <i class="fa fa-plus"></i></button></a>'
                        . ' <div class="modal fade" tabindex="-1" role="dialog" id="modalForAddCoupon"> '
                        . '<div class="modal-dialog  modal-md">'
                        . '<div class="modal-content all_data_cus_modal">'
                        . '</div>'
                        . '</div>'
                        . '</div>';
                // show coupon generate dialog
            } else if ($orderInfo['order_status'] == 'coupon_generated') { // shipped the generated coupon 
                $defaultLabel = 'C. Generated';
                $CODActionArray = array('Shipped');
                //$html .='<button type="button" class="btn btn-warning disabled" title="Coupon Generated">CG</button>';
            } elseif ($orderInfo['order_status'] == 'shipped') { // shipped the generated coupon 
                $defaultLabel = 'Shipped';
                $CODActionArray = array('Wrong Shipping Address', 'Delivered', 'Delivery Failed');
            } elseif ($orderInfo['order_status'] == 'wrong_shipping_address') {
                $defaultLabel = 'Wrong Shipping Address';
                $CODActionArray = array('Shipped');
            } elseif ($orderInfo['order_status'] == 'delivery_failed') {
                $defaultLabel = 'Delivery Failed';
                $CODActionArray = array('Shipped', 'Cancel');
            } else if ($orderInfo['order_status'] == 'delivered') {
                $defaultLabel = 'Delivered';
                $CODActionArray = array('Delivered');
            } else if ($orderInfo['order_status'] == 'cancel') {
                $defaultLabel = 'Cancel';
                $CODActionArray = array('Cancel');
            } elseif ($orderInfo['order_status'] == 'completed') {
                $defaultLabel = 'Completed';
                $CODActionArray = array('Completed');
            }


            if (is_array($CODActionArray) > 0) {
                $class = '';
                $caretHidden = '';
                $display = '';
                $button_class = 'danger';
                if ($orderInfo['order_status'] == 'confirmed') {
                    $class = ' none';
                    $display = 'none';
                    $caretHidden = ' hidden';
                }
                if ($orderInfo['order_status'] == 'delivered') {
                    $button_class = 'success';
                    $class = ' disabled';
                    $caretHidden = ' hidden';
                }
                if ($orderInfo['order_status'] == 'completed') {
                    $button_class = 'success';
                    $class = ' disabled';
                    $caretHidden = ' hidden';
                }
                if ($orderInfo['order_status'] == 'cancel') {
                    $button_class = 'warning';
                    $class = ' disabled';
                    $caretHidden = ' hidden';
                }

                $html .= '<br><div class="btn-group" style="display:' . $display . '">
                <button type="button" class="btn btn-' . $button_class . '' . $class . '">' . $defaultLabel . '</button>
                <button type="button" class="btn btn-danger dropdown-toggle ' . $caretHidden . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button><br/>' . $couponCode . '<br/>';

                if ($orderInfo['order_status'] == 'pending' || ($orderInfo['order_status'] == 'waiting_for_confirmed') || ($orderInfo['order_status'] == 'confirmed') || ($orderInfo['order_status'] == 'cancel')) {
                    $html .= '';
                } else {
                    $html .= '<a target="_blank" href="' . base_url('subscription/orders/generateInvoiceInPDF') . '?orderId=' . $orderInfo['id'] . '">PDF</a>';
                }

                $html .= '<ul class="dropdown-menu">';

                foreach ($CODActionArray as $actionLabel) {
                    $status = preg_replace('/\s+/', '_', $actionLabel);

                    $html .= '<li>'
                            . '<a data-toggle="modal" href="' . base_url('/subscription/orders/changeStatusForm/' . $orderInfo['id'] . '/' . $status) . '" title="' . $actionLabel . '" data-target="#modalForChangeStatus_' . $status . '">' . $actionLabel . '</a></li>';
                }

                $html .= '</ul></div>';

                foreach ($CODActionArray as $actionLabel) {
                    $status = preg_replace('/\s+/', '_', $actionLabel);
                    $html .= '<div class="modal fade"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel" id="modalForChangeStatus_' . $status . '">
                        <div class="modal-dialog modal-md">
                            <div class="modal-content">

                            </div>
                        </div>
                    </div>';
                }
            }
        }

        return $html;
    }

}

if (!function_exists('createPagination')) {

    function createPagination($page_url, $total_rows, $per_page, $uri_segment = 3, $num_links = 8) {
        $CI = &get_instance();
//load the pagging library
        $CI->load->library('pagination');
        $suffix = '';
        if ($getItems = $CI->input->get()) {
            $itemCount = 1;
            foreach ($getItems as $key => $value) {
                $suffix .= $itemCount > 1 ? '&' : '';
                $suffix .= $key . '=' . $value;
                $itemCount++;
            }
        }
// set the configuration
        $config['base_url'] = $page_url;
        $config['suffix'] = '?' . $suffix;
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['uri_segment'] = $uri_segment;
        $config['num_links'] = $num_links;
        $config['first_url'] = $config['base_url'] . $config['suffix'];

//pagging design sectionopen full tag
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['first_link'] = '&laquo; First';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_link'] = 'Last &raquo;';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['next_link'] = 'Next &rsaquo;';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['prev_link'] = '&lsaquo; Previous';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a href="javascript:void(0)">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $CI->pagination->initialize($config);

        return $CI->pagination->create_links();
    }

}

if (!function_exists('managementPanel')) {

    function managementPanel($parameter) {
        // get the CI instanse
        $CI = &get_instance();
        $CI->load->model('admin_model');

        $managementData = $CI->admin_model->getUserManagementPanelData();

        $html = '';
        if (!empty($managementData)) {
            $totalUsers = $managementData['total_users'] ? $managementData['total_users'] : '0';
            //$totalActiveUsers = $managementData['total_active_users'] ? $managementData['total_active_users'] : 0;
            $totalInactiveUsers = $managementData['total_inactive_users'] ? $managementData['total_inactive_users'] : 0;
            $totalDisabledUsers = $managementData['total_desabled_users'] ? $managementData['total_desabled_users'] : 0;
            $totalSuspendedUsers = $managementData['total_suspended_users'] ? $managementData['total_suspended_users'] : 0;
            $totalSubscribedUsers = $managementData['total_subscribed_users'] ? $managementData['total_subscribed_users'] : 0;
            $totalVerifiedUsers = $managementData['total_verified_users'] ? $managementData['total_verified_users'] : 0;
            $totalNonVerifiedUsers = $totalUsers - $totalVerifiedUsers;
            $totalFlagedUsers = $managementData['total_flaged_users'] ? $managementData['total_flaged_users'] : 0;
            $totalWinners = $managementData['total_winners'] ? $managementData['total_winners'] : 0;
            $totalPrizeBond = $managementData['total_prizebond'] ? $managementData['total_prizebond'] : 0;
            $totalDraws = $managementData['total_draws'] ? $managementData['total_draws'] : 0;
            $totalSalesAmount = $managementData['total_sales_amount'] ? $managementData['total_sales_amount'] : 0;
            $totalOrder = $managementData['total_order'] ? $managementData['total_order'] : 0;

            if ($parameter == 'user_management') {
                $html .= '<div class="panel panel-default">
                <div class="panel-heading">
                    Statistics
                </div>
                <div class="panel-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="manage-users-block manage-users-block-info">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-users" aria-hidden="true"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">' . $totalUsers . '</div>
                                    <label>Total Registered Users</label>
                                </div>
                            </div> 
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="manage-users-block manage-users-block-yellow">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-user" aria-hidden="true"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">' . $totalNonVerifiedUsers . '</div>
                                    <label>Non Verified Users</label>
                                </div>
                            </div> 
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="manage-users-block manage-users-block-warning">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-user-o" aria-hidden="true"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">' . $totalInactiveUsers . '</div>
                                    <label>Inactive Users</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="manage-users-block manage-users-block-gray">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-user-o" aria-hidden="true"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">' . $totalDisabledUsers . '</div>
                                    <label>Disabled Users</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                    <div class="row" style ="margin-top:15px;">
                    <div class="col-lg-3 col-md-6">
                        <div class="manage-users-block manage-users-block-info">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-user-o" aria-hidden="true"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">' . $totalSuspendedUsers . '</div>
                                    <label>Suspended Users</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="manage-users-block manage-users-block-success">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-user-o" aria-hidden="true"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">' . $totalSubscribedUsers . '</div>
                                    <label>Subscribed Users</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="manage-users-block manage-users-block-green">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-user-o" aria-hidden="true"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">' . $totalVerifiedUsers . '</div>
                                    <label>Verified Users</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="manage-users-block manage-users-block-red">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-user-o" aria-hidden="true"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">' . $totalFlagedUsers . '</div>
                                    <label>Flaged Users</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>';
            } elseif ($parameter == 'dashboard') {
                $html .= '    <div class="row home-panel-block">
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-users fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">' . $totalUsers . '</div>
                            <div>Total Registered Users</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-users fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">' . $totalSubscribedUsers . '</div>
                            <div>Total Subscribers!</div>
                        </div>
                    </div>
                </div>
          
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-green">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-users fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">' . $totalVerifiedUsers . '</div>
                            <div>Total Verified Users</div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-red">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-flag fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">' . $totalFlagedUsers . '</div>
                            <div>Total Flaged Users</div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    <!-- /.row -->

    <div class="row  home-panel-block">
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-green">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-trophy fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">' . $totalWinners . '</div>
                            <div>Total Winning Prizebond</div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-green">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-ticket fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">' . $totalPrizeBond . '</div>
                            <div>Total Prize Bonds</div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
<div class="panel panel-info">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-ticket fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">' . $totalDraws . '</div>
                            <div>Total Draws</div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
<div class="panel panel-yellow">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-users fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">' . $totalNonVerifiedUsers . '</div>
                            <div>Total Non Verified Users</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
<div class="row  home-panel-block">
<div class="col-lg-3 col-md-6">
<div class="panel panel-red">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-users fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">' . $totalInactiveUsers . '</div>
                            <div>Total Inactive Users</div>
                        </div>
                    </div>
                </div>
   
            </div>
</div>
<div class="col-lg-3 col-md-6">
<div class="panel panel-yellow">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-users fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">' . $totalDisabledUsers . '</div>
                            <div>Total Disabled Users</div>
                        </div>
                    </div>
                </div>

            </div>
</div>
<div class="col-lg-3 col-md-6">
<div class="panel panel-red">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-users fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">' . $totalSuspendedUsers . '</div>
                            <div>Total Suspended Users</div>
                        </div>
                    </div>
                </div>

            </div>
</div>
<div class="col-lg-3 col-md-6">
<div class="panel panel-info">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-dollar fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">' . $totalSalesAmount . '</div>
                            <div>Total ' . $totalOrder . ' Sales</div>
                        </div>
                    </div>
                </div>
              
            </div>
</div>

</div>
';
            }

            return $html;
        }
    }

}

if (!function_exists('convertEnglishNumberIntoBanglaNumber')) {

    function convertEnglishNumberIntoBanglaNumber($number) {
        $englishNumber = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0);
        $banglaNumber = array('১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০');
        $convertNumber = str_replace($englishNumber, $banglaNumber, $number);
        return $convertNumber;
    }

}
if (!function_exists('convertBanglaNumberIntoEnglishNumber')) {

    function convertBanglaNumberIntoEnglishNumber($number) {
        $banglaNumber = array('১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০');
        $englishNumber = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0);
        $convertNumber = str_replace($banglaNumber, $englishNumber, $number);
        return $convertNumber;
    }

}
if (!function_exists('convertMoneyFormat')) {

    function convertMoneyFormat($amount, $currency_code = 'bn_BD') {
        setlocale(LC_MONETARY, $currency_code);
        return money_format('%i', $amount);
    }

}

if (!function_exists('getSeriesImage')) {

    function getSeriesImage($series) {
        switch ($series) {
            case 'কক':
                $imageName = '01.png';
                break;
            case 'কখ':
                $imageName = '02.png';
                break;
            case 'কগ':
                $imageName = '03.png';
                break;
            case 'কঘ':
                $imageName = '04.png';
                break;
            case 'কঙ':
                $imageName = '05.png';
                break;
            case 'কচ':
                $imageName = '06.png';
                break;
            case 'কছ':
                $imageName = '07.png';
                break;
            case 'কজ':
                $imageName = '08.png';
                break;
            case 'কঝ':
                $imageName = '09.png';
                break;
            case 'কঞ':
                $imageName = '10.png';
                break;
            case 'কট':
                $imageName = '11.png';
                break;
            case 'কঠ':
                $imageName = '12.png';
                break;
            case 'কড':
                $imageName = '13.png';
                break;
            case 'কঢ':
                $imageName = '14.png';
                break;
            case 'কথ':
                $imageName = '15.png';
                break;
            case 'কদ':
                $imageName = '16.png';
                break;
            case 'কন':
                $imageName = '17.png';
                break;
            case 'কপ':
                $imageName = '18.png';
                break;
            case 'কফ':
                $imageName = '19.png';
                break;
            case 'কব':
                $imageName = '20.png';
                break;
            case 'কম':
                $imageName = '21.png';
                break;
            case 'কল':
                $imageName = '22.png';
                break;
            case 'কশ':
                $imageName = '23.png';
                break;
            case 'কষ':
                $imageName = '24.png';
                break;
            case 'কস':
                $imageName = '25.png';
                break;
            case 'কহ':
                $imageName = '26.png';
                break;
            case 'খক':
                $imageName = '27.png';
                break;
            case 'খখ':
                $imageName = '28.png';
                break;
            case 'খগ':
                $imageName = '29.png';
                break;
            case 'খঘ':
                $imageName = '30.png';
                break;
            case 'খঙ':
                $imageName = '31.png';
                break;
            case 'খচ':
                $imageName = '32.png';
                break;
            case 'খছ':
                $imageName = '33.png';
                break;
            case 'খজ':
                $imageName = '34.png';
                break;
            case 'খঝ':
                $imageName = '35.png';
                break;
            case 'খঞ':
                $imageName = '36.png';
                break;
            case 'খট':
                $imageName = '37.png';
                break;
            case 'খঠ':
                $imageName = '38.png';
                break;
            case 'খড':
                $imageName = '39.png';
                break;
            case 'খঢ':
                $imageName = '40.png';
                break;
            case 'খথ':
                $imageName = '41.png';
                break;
            case 'খদ':
                $imageName = '42.png';
                break;
            case 'খন':
                $imageName = '43.png';
                break;
            case 'খপ':
                $imageName = '44.png';
                break;
            case 'খফ':
                $imageName = '45.png';
                break;
            case 'খব':
                $imageName = '46.png';
                break;
            case 'খম':
                $imageName = '47.png';
                break;
            case 'খল':
                $imageName = '48.png';
                break;
        }
        return $imageName;
    }

}
    