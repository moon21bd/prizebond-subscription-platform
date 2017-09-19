<link href="<?php echo base_url('assets/css/style.css'); ?>" rel="stylesheet">
<style>
    .btn-group > .btn:first-child:not(:last-child):not(.dropdown-toggle) {
        border-bottom-right-radius: 3px;
        border-top-right-radius: 3px;
    }

    /*    .modal-dialog {
            width: 98%;
            height: 92%;
            padding: 0;
        }
    
        .modal-content {
            height: 99%;
        }*/
</style>
<body>
    <div id="wrapper">
        <div id="page-wrapper">

            <div class="row">
                <div class="col-lg-12"> 
                    <div class="tracking-data-heading">
                        <div class="col-lg-4 pl-0">
                            <h3> <?php echo $title; ?> </h3>
                        </div>
                        <?php if ($this->session->userdata('userRole') != Main::USER_ROLE_CRM) { ?>
                            <div class="col-lg-3 pr-0 pull-right">
                                <div class="form-group form-group-0">
                                    <form method="POST" action="" id="dateRangeForm"> 
                                        <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px; border: 1px solid #ccc; width:235px; position:absolute; right:0; left:inherit !important; text-align: center">
                                            <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
                                            <span></span> <b class="caret"></b>
                                        </div>
                                        <input type="hidden" name="start_date" id="start_date" value="<?php $this->input->get('start_date'); ?>">
                                        <input type="hidden" name="end_date" id="end_date" value="<?php $this->input->get('end_date'); ?>">
                                    </form> 
                                </div>
                            </div>
                        <?php } ?>

                    </div>
                </div>   
                <div class="clearfix"></div>
            </div>


            <?php if ($this->session->flashdata('success_msg')) { ?>
                <div role="alert" class="alert alert-success alert-dismissible fade in">
                    <button aria-label="Close" data-dismiss="alert" class="close" type="button"><span aria-hidden="true">×</span></button>
                    <?php echo $this->session->flashdata('success_msg'); ?>
                </div>
            <?php } ?>

            <form method="GET" action="<?php echo base_url("subscription/orders/history/"); ?>">

                <?php
                if ($this->input->get()) {
                    foreach ($this->input->get() as $key => $val) {
                        echo '<input type="hidden" name="' . $key . '" id="' . $key . '" value="' . $val . '" />';
                    }
                }
                ?>
                <div class="col-lg-2">
                    <div class="form-group">
                        <input type="text" name="search_query" class="form-control" value="<?php echo $this->input->get('search_query'); ?>" placeholder="Order ID, User Name">
                    </div>
                </div>
                <?php if ($this->session->userdata('userRole') != Main::USER_ROLE_CRM) { ?>
                    <div class="col-lg-3">
                        <?php
                        echo form_dropdown('payment_name', $paymentMethod, $this->input->get('payment_name'), array('class' => 'form-control'));
                        ?>
                    </div>
                    <div class="col-lg-2" >
                        <select name="payment_status" class="form-control">
                            <option value=""> Payment Status Any</option> 
                            <option value="pending" <?php echo $this->input->get('payment_status') && $this->input->get('payment_status') == 'pending' ? 'selected' : '' ?>>Payment Status - Pending</option> 
                            <option value="success" <?php echo $this->input->get('payment_status') && $this->input->get('payment_status') == 'success' ? 'selected' : '' ?>>Payment Status - Success</option> 
                            <option value="failure" <?php echo $this->input->get('payment_status') && $this->input->get('payment_status') == 'failure' ? 'selected' : '' ?>>Payment Status - Failure</option> 
                        </select>
                    </div>
                    <div class="col-lg-2" >
                        <select name="order_status" class="form-control">
                            <option value="">Order Status Any</option> 
                            <option value="pending" <?php echo $this->input->get('order_status') && $this->input->get('order_status') == 'pending' ? 'selected' : '' ?>>Order Status - Pending</option> 
                            <option value="failed" <?php echo $this->input->get('order_status') && $this->input->get('order_status') == 'failed' ? 'selected' : '' ?>>Order Status - Failed</option> 
                            <option value="completed" <?php echo $this->input->get('order_status') && $this->input->get('order_status') == 'completed' ? 'selected' : '' ?>>Order Status - Completed</option> 
                            <option value="confirmed" <?php echo $this->input->get('order_status') && $this->input->get('order_status') == 'confirmed' ? 'selected' : '' ?>>Order Status - Confirmed</option> 
                            <option value="shipped" <?php echo $this->input->get('order_status') && $this->input->get('order_status') == 'shipped' ? 'selected' : '' ?>>Order Status - Shipped</option> 
                            <option value="delivered" <?php echo $this->input->get('order_status') && $this->input->get('order_status') == 'delivered' ? 'selected' : '' ?>>Order Status - Delivered</option> 
                            <option value="wrong_shipping_address" <?php echo $this->input->get('order_status') && $this->input->get('order_status') == 'wrong_shipping_address' ? 'selected' : '' ?>>Order Status - Wrong Shipping Address</option> 
                            <option value="delivery_failed" <?php echo $this->input->get('order_status') && $this->input->get('order_status') == 'delivery_failed' ? 'selected' : '' ?>>Order Status - Delivery Failed</option> 
                            <option value="waiting_for_confirmed" <?php echo $this->input->get('order_status') && $this->input->get('order_status') == 'waiting_for_confirmed' ? 'selected' : '' ?>>Order Status - Waiting For Confirmed</option> 
                            <option value="cancel" <?php echo $this->input->get('order_status') && $this->input->get('order_status') == 'cancel' ? 'selected' : '' ?>>Order Status - Cancel</option> 
                            <option value="coupon_generated" <?php echo $this->input->get('order_status') && $this->input->get('order_status') == 'coupon_generated' ? 'selected' : '' ?>>Order Status - Coupon Generated</option> 
                        </select>
                    </div>
                    <div class="col-lg-2" >
                        <select name="product" class="form-control">
                            <option value=""> Product Any</option> 
                            <option value="subscription" <?php echo $this->input->get('product') && $this->input->get('product') == 'subscription' ? 'selected' : '' ?>>Product - Subscription</option> 
                            <option value="prizebond" <?php echo $this->input->get('product') && $this->input->get('product') == 'prizebond' ? 'selected' : '' ?>>Product - Prizebond</option> 
                        </select>
                    </div>
                <?php } ?>
                <div class="col-lg-1">
                    <button class="btn btn-success" type="submit" name="search" value="1"> Search</button>
                </div>
            </form>

            <div class="row">
                <div class="col-lg-12">
                    <div class="panel-body" style="padding:0px;">
                        <div class="table-responsive">
                            <table width="100%" class="table" style="font-size: 12px;">
                                <colgroup>
                                    <col width="15%">
                                    <col width="30%">
                                    <col width="20%">
                                    <col width="20%">
                                    <col width="15%">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th class="text-center">Order Info</th>
                                        <th class="text-center">User Info</th>
                                        <th  class="text-center">Product Info</th>
                                        <th class="text-center">Payment Info</th>
                                        <th  class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (is_array($orderList) && (count($orderList))) {
                                        foreach ($orderList as $key => $orderInfo) {
                                            ?>
                                            <tr>

                                                <td class="text-left">
                                                    <b>ID : </b> <a data-toggle="modal" href="<?php echo base_url('subscription/orders/view/' . $orderInfo['id']); ?>" title="view" data-target="#order_list_<?php echo $orderInfo['id']; ?>"><?php echo ($orderInfo['order_id']) ? $orderInfo['order_id'] : 'N/A' . '&#xA;'; ?></a>
                                                    <br/>On <?php echo date('j M, Y h:i:s a', strtotime($orderInfo['order_created_date_time'])); ?>
                                                    <?php
                                                    if ($orderInfo['payment_method_type_name'] == 'bKash' && $orderInfo['payment_status'] == 'success') {
                                                        echo!empty($orderInfo['payment_note']) ? '<br/> <b>Note : </b>' . $orderInfo['payment_note'] : 'N/A';
                                                    }
                                                    ?>
                                                </td>

                                                <?php echo miniUserProfile($orderInfo['user_id']); ?>


                                                <td class="text-left">
                                                    <a data-toggle="modal" href="<?php echo base_url('subscription/orders/productView/' . $orderInfo['product_id']); ?>" title="view" data-target="#product_<?php echo $orderInfo['product_id']; ?>"> <?php echo ($orderInfo['product_name']) ? $orderInfo['product_name'] : 'N/A'; ?></a>
                                                    - Tk. <?php echo ($orderInfo['price']) ? number_format((float) $orderInfo['price'], 2, '.', '') : 'N/A'; ?><br>
                                                    <?php if ($orderInfo['product_id'] == 26) { ?>
                                                        - Quantity : 
                                                        <?php
                                                        echo $orderInfo['quantity'];
                                                    }
                                                    ?>
                                                </td>

                                                <!--End day 17 April-->
                                                <td class="text-left">
                                                    <?php
                                                    echo!empty($orderInfo['payment_method_type_name']) ? $orderInfo['payment_method_type_name'] . ' <b> - </b>' : 'N/A';
                                                    if ($orderInfo['payment_status'] == 'pending') {
                                                        ?>
                                                        <span style="color: #f4a941;">pending</span>
                                                    <?php } elseif ($orderInfo['payment_status'] == 'success') { ?>
                                                        <span style="color:green;">success</span>   
                                                    <?php } else { ?>
                                                        <span style="color:red;">failure</span>  
                                                        <?php
                                                    }
                                                    ?>
                                                    <br><span style="font-size: 10px;">
                                                        Total: <?php echo $orderInfo['total_receivable_amount'] ?>, Shipping: <?php echo $orderInfo['shipping_cost'] ?>, Discount: <?php echo $orderInfo['discount'] ?>
                                                    </span>
                                                    <br/>
                                                    <?php if ($orderInfo['payment_date_time'] != '0000-00-00 00:00:00') { ?>
                                                        <a title="<?php echo!empty($orderInfo['payment_date_time']) ? date('M j, Y H:i:s', strtotime($orderInfo['payment_date_time'])) : 'N/A'; ?>" href="javascript:void(0)"> <?php echo date('j M, Y h:i a', strtotime($orderInfo['payment_date_time'])); ?></a>
                                                        <?php
                                                    } else {
                                                        echo '';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php
                                                    if ($orderInfo['payment_method_type_id'] == 7) {
                                                        echo CODActionsHelper($orderInfo);
                                                    } else {
                                                        if ($orderInfo['product_id'] != 26 || $orderInfo['payment_status'] != 'success') {
                                                            echo 'N/A<br>';
                                                        }
                                                    }

                                                    if ($orderInfo['product_id'] == 26 && ($orderInfo['payment_status'] == 'success') && ($orderInfo['prizebond'] == NULL)) {
                                                        ?>
                                                        <a data-toggle="modal" href="<?php echo base_url('subscription/orders/addPrizeBond/' . $orderInfo['id']); ?>" title="Add prizebond" data-target="#addPrizeBond_<?php echo $orderInfo['id']; ?>"><button class="btn btn-primary btn-circle" type="button"><i class="fa fa-plus"></i> </button></a>
                                                        <?php
                                                    } elseif ($orderInfo['product_id'] == 26 && ($orderInfo['payment_status'] == 'success') && ($orderInfo['prizebond'] != NULL) && ($orderInfo['delivery_id'] == NULL)) {?>
                                                        <a data-toggle="modal" href="<?php echo base_url('subscription/orders/addPrizeBondDeliveryId/' . $orderInfo['id']); ?>" title="Add delivery ID" data-target="#addPrizeBondDeliveryID_<?php echo $orderInfo['id']; ?>"><button class="btn btn-success btn-circle" type="button"><i class="fa fa-plus"></i> </button></a>
                                                    <?php } elseif ($orderInfo['product_id'] == 26 && ($orderInfo['payment_status'] == 'success') && ($orderInfo['prizebond'] != NULL)&& ($orderInfo['delivery_id'] != NULL)) {?>
                                                        <a class="btn btn-success" title="Download Prizebond PDF" target="_blank" href="<?php echo base_url('subscription/orders/generateInvoiceForAuthorityAndCutomerPDF?orderId='. $orderInfo['id']);?>"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF</a><br><br>
                                                   <?php } ?>
                                                </td>

                                            </tr>



                                        <div class="modal fade"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel" id="user_<?php echo $orderInfo['user_id']; ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">

                                                </div><!-- /.modal-content -->
                                            </div><!-- /.modal-dialog -->
                                        </div><!-- /.modal -->
                                        <div class="modal fade"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel" id="order_list_<?php echo $orderInfo['id']; ?>">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">

                                                </div><!-- /.modal-content -->
                                            </div><!-- /.modal-dialog -->
                                        </div><!-- /.modal -->
                                        <div class="modal fade"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel" id="product_<?php echo $orderInfo['product_id']; ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">

                                                </div><!-- /.modal-content -->
                                            </div><!-- /.modal-dialog -->
                                        </div><!-- /.modal -->

                                        <div class="modal fade"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel" id="addPrizeBond_<?php echo $orderInfo['id']; ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">

                                                </div><!-- /.modal-content -->
                                            </div><!-- /.modal-dialog -->
                                        </div><!-- /.modal -->
                                        <div class="modal fade"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel" id="addPrizeBondDeliveryID_<?php echo $orderInfo['id']; ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">

                                                </div><!-- /.modal-content -->
                                            </div><!-- /.modal-dialog -->
                                        </div><!-- /.modal -->

                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr><td class="text-center" colspan="8">
                                            Data not Found!!
                                        </td>
                                    </tr>
                                <?php } ?>

                                </tbody>
                            </table>

                            <div class="pull-right">
                                <?php echo $this->pagination->create_links(); ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .order-list-heading h3 {
            margin: 2px 0 0;
            padding: 0;
            font-weight: 600;
        }
        .order-list-heading {
            border-bottom: 1px solid #f1f1f1;
            margin: 20px auto 30px;
            overflow: hidden;
            padding-bottom: 15px;
        }
    </style>


