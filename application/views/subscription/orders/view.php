<style>
    .table > tbody > tr > td,
    tbody tr td{
        padding:8px 8px 10px 8px;
        color:#4c4c4c;
        border-top: 0 none;
        background-color:#fff;
        border-bottom: 2px solid #efefef !important;
        /* box-shadow: 0 2px 3px #e0e0e0;*/
    }
    .panel-body-pt{
        padding-top:4px !important;
    } 
</style>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close" ><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">Order No : <?php echo!empty($orderListInfo->id) ? $orderListInfo->id : 'N/A'; ?> </h4>
</div>

<div class="modal-body">
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Order Info
                </div>
                <div class="panel-body">
                    <table>
                        <tbody>
                            <tr>
                                <td width="340" class="text-left" title="Order Reference ID"><strong>ID</strong></td>
                                <td width="273" title="Order Status"><strong>Status</strong></td>
                                <td width="743" title="Total Receivable Amount"><strong>Amount</strong></td>
                                <td width="743"><strong>Address</strong></td>
                                <td width="743"><strong>Order Date</strong></td>
                                <td width="743" title="Applicable only physical bond"><strong>Delivery Date</strong></td>
                            </tr>
                            <tr>
                                <td class="text-left"><?php
                                    echo!empty($orderListInfo->order_id) ? $orderListInfo->order_id : 'N/A';
                                    ?> </td>
                                <td>
                                    <?php
                                    if (strpos($orderListInfo->order_status, '_') > 0) {
                                        echo!empty($orderListInfo->order_status) ? str_replace('_', ' ', ucfirst($orderListInfo->order_status)) : 'N/A';
                                    } else {
                                        echo!empty($orderListInfo->order_status) ? ucfirst($orderListInfo->order_status) : 'N/A';
                                    }
                                    ?> 
                                </td>
                                <td><?php echo!empty($orderListInfo->total_receivable_amount) ? $orderListInfo->total_receivable_amount : 'N/A'; ?> </td>   
                                <td><?php echo!empty($orderListInfo->shipping_address) ? $orderListInfo->shipping_address : 'N/A'; ?> </td>   
                                <td>
                                    <?php
                                    if (($orderListInfo->order_created_date_time != '0000-00-00 00:00:00') || (!empty($orderListInfo->order_created_date_time))) {
                                        echo date('M j, Y', strtotime($orderListInfo->order_created_date_time)) . '<br>' . date('h:i:s A', strtotime($orderListInfo->order_created_date_time));
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?> 
                                </td>
                                <td>
                                    <?php
                                    if ($productInfo['product_cat'] == 'Physical Bond') {
                                        $deliveryDate = date('Y-m-d H:i:s', strtotime($orderListInfo->order_created_date_time . ' +10 day'));
                                        echo date('M j, Y', strtotime($deliveryDate)) . '<br>' . date('h:i:s A', strtotime($deliveryDate));
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                            </tr> 
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-info">
                <div class="panel-heading">
                    Product Info
                </div>
                <div class="panel-body panel-body-pt">
                    <div class="row">
                        <div class="col-lg-12">
                            <table class="table"  style=" margin-bottom: 0">
                                <tbody>
                                    <tr>
                                        <td><strong>Product Name</strong></td>
                                        <td><strong>Quantity</strong></td>
                                        <td><strong>Price</strong></td>
                                        <td><strong>Payment Date</strong></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo!empty($productInfo['name']) ? $productInfo['name'] : 'N/A'; ?> </td>
                                        <td><?php echo $orderListInfo->quantity; ?> </td>
                                        <td><?php echo!empty($productInfo['attributeName']['Price']) ? $productInfo['attributeName']['Price'] : '0'; ?> </td>
                                        <td><?php
                                            if ($orderListInfo->payment_date_time != '0000-00-00 00:00:00') {
                                                echo date('M j, Y h:i:s A', strtotime($orderListInfo->payment_date_time));
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php if (!empty($orderListInfo->prizebond)) { ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Prizebond List - <b>Delivery ID : </b>
                        <?php echo (!empty($orderListInfo->delivery_id)) ? $orderListInfo->delivery_id : 'N/A'; ?>
                    </div>
                    <div class="panel-body">
                        
                        <table>
                            <?php
                            $i = 0;
                            $prizebondArray = explode(',', $orderListInfo->prizebond);
                            foreach (array_chunk($prizebondArray, 7) as $row) {
                                ?>
                                <tr style=" margin-bottom: 50px">
                                    <?php
                                    foreach ($row as $key => $val) {
                                        $i++;
                                        ?>
                                        <td style=" width: 180px; color: #303030; font-size: 12px; margin: 0 0px 15px 0;"><?php echo sprintf("%02d", $i) . '. ' .$val; ?></td>
                                    <?php } ?>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>







    <?php if ($orderListInfo->payment_method_type_id == 7 && (!empty($CODInfo['coupon']))) {
        ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        COD Info
                    </div>
                    <div class="panel-body panel-body-pt">
                        <div class="row">
                            <div class="col-lg-12">
                                <table class="table" style=" margin-bottom: 0">
                                    <thead>
                                        <tr>
                                            <th><strong>Code</strong></th>
                                            <th><strong>Note</strong></th>
                                            <th><strong>Status</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (!empty($CODInfo)) {
                                            ?>
                                            <tr>
                                                <td><?php echo!empty($CODInfo['coupon']) ? $CODInfo['coupon'] : 'N/A'; ?> </td>
                                                <td><?php echo!empty($CODInfo['note']) ? $CODInfo['note'] : 'N/A'; ?> </td>
                                                <td><?php echo!empty($CODInfo['status']) ? ucfirst($CODInfo['status']) : 'N/A'; ?> </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php
    if ($orderListInfo->payment_status == 'success') {
        $panelClass = 'success';
    } elseif ($orderListInfo->payment_status == 'pending') {
        $panelClass = 'default';
    } elseif ($orderListInfo->payment_status == 'failure') {
        $panelClass = 'danger';
    }
    ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-<?php echo $panelClass; ?>">
                <div class="panel-heading">
                    Payment Info
                </div>
                <div class="panel-body panel-body-pt">
                    <table class="table" style=" margin-bottom: 0">
                        <tbody>
                            <tr>
                                <td width="276"><strong>Payment Method</strong></td>
                                <td width="252"><strong>Payment Status</strong></td>
                                <td width="235"><strong>Total</strong></td>
                                <td width="233"><strong>Shipping</strong></td>
                                <td width="154"><strong>Discount</strong></td>
                            </tr>
                            <tr>
                                <td><?php echo!empty($orderListInfo->payment_method_type_name) ? $orderListInfo->payment_method_type_name : 'N/A'; ?> </td>
                                <td><?php echo!empty($orderListInfo->payment_status) ? ucfirst($orderListInfo->payment_status) : 'N/A'; ?> </td>
                                <td><?php echo!empty($orderListInfo->total_receivable_amount) ? $orderListInfo->total_receivable_amount : '0'; ?> </td>
                                <td><?php echo!empty($orderListInfo->shipping_cost) ? $orderListInfo->shipping_cost : '0'; ?> </td>
                                <td><?php echo!empty($orderListInfo->discount) ? $orderListInfo->discount : '0'; ?> </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
    <?php echo userProfileInfoPanel($users_personal_info); ?> 


</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>



