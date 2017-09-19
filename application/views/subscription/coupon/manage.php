<body>
    <div id="wrapper">
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h3 class="page-header">Manage Coupons</h3>
                </div>
            </div>

            <?php if ($this->session->flashdata('success_msg')) { ?>
                <div role="alert" class="alert alert-success alert-dismissible fade in">
                    <button aria-label="Close" data-dismiss="alert" class="close" type="button"><span aria-hidden="true">×</span></button>
                    <?php echo $this->session->flashdata('success_msg'); ?>
                </div>
            <?php } ?>

            <div class="row">
                <div class="col-lg-12">
                    <span class="pull-right" style="margin-bottom: 8px;">
                        <a class="btn btn-success btn-group-xs" href="<?php echo base_url('subscription/coupon/add') ?>"><i class="fa fa-plus "></i> Add New</a>
                    </span>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center">SL#</th>
                                        <th class="text-center">Product Name</th>
                                        <th class="text-center">Series - Coupon</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Added Date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (is_array($couponList) && (count($couponList))) {
                                        foreach ($couponList as $key => $couponInfo) {
                                            ?>
                                            <tr>
                                                <td class="text-center"><?php echo $key + 1; ?></td>
                                                <td class="text-center"><?php echo!empty($couponInfo['product_list']) ? $couponInfo['product_list'] : 'N/A'; ?></td>
                                                <?php if ($couponInfo['product_status'] == 0) { ?>
                                                    <td class="text-center"><strike><?php echo!empty($couponInfo['name']) ? $couponInfo['series_id'] . ' - ' . $couponInfo['name'] : 'N/A'; ?></strike></td>
                                        <?php } else { ?>
                                            <td class="text-center"><?php echo!empty($couponInfo['name']) ? $couponInfo['series_id'] . '-' . $couponInfo['name'] : 'N/A'; ?></td>
                                        <?php } ?>
                                        <td class="text-center">
                                            <?php if ($couponInfo['status'] == 'used') { ?>
                                                <span style="color:green;font-weight:bold">Used</span>

                                            <?php } else { ?>
                                                <span style="color:red;font-weight:bold">Not Used</span>  
                                            <?php } ?>
                                        </td>
                                        <td class="text-center"><a href="javascript:void(0)" title="<?php echo!empty($couponInfo['created_date_time']) ? date('M j, Y H:i A', strtotime($couponInfo['created_date_time'])) : 'N/A'; ?>"><?php echo!empty($couponInfo['added_date']) ? $couponInfo['added_date'] : 'N/A'; ?></a></td>

                                        <td class="text-center">
                                            <a data-toggle="modal" href="<?php echo base_url('subscription/coupon/view/' . $couponInfo['id']); ?>" title="view" data-target="#coupon_<?php echo $couponInfo['id']; ?>" ><button class="btn btn-success btn-circle" type="button"><i class="fa fa-eye"></i> </button></a>
                                            <?php if ($couponInfo['status'] == 'used') { ?>
                                                <a href="<?php echo base_url('subscription/coupon/delete/' . $couponInfo['id']); ?>" onclick="return confirm('Do You Want to  Delete this coupon?');" title="delete"><button class="btn btn-warning btn-circle  disabled" type="button"><i class="fa fa-times"></i> </button></a>

                                            <?php } else { ?>
                                                <a href="<?php echo base_url('subscription/coupon/delete/' . $couponInfo['id']); ?>" onclick="return confirm('Do You Want to  Delete this coupon?');" title="delete"><button class="btn btn-warning btn-circle" type="button"><i class="fa fa-times"></i> </button></a>

                                            <?php } ?>
                                        </td>
                                        </tr>
                                        <div class="modal fade"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel" id="coupon_<?php echo $couponInfo['id']; ?>">
                                            <div class="modal-dialog" >
                                                <div class="modal-content">

                                                </div><!-- /.modal-content -->
                                            </div><!-- /.modal-dialog -->
                                        </div><!-- /.modal -->

                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr><td class="text-center" colspan="9">
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
</div>


