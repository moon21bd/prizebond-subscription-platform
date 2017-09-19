<body>
    <div id="wrapper">
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h3 class="page-header">Add Coupon </h3>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel panel-default">
                        <!-- /.panel-heading -->
                        <?php if ($this->session->flashdata('success_msg')) { ?>
                            <div role="alert" class="alert alert-success alert-dismissible fade in">
                                <button aria-label="Close" data-dismiss="alert" class="close" type="button"><span aria-hidden="true">×</span></button>
                                <strong>Success!</strong><br>
                                <?php echo $this->session->flashdata('success_msg'); ?>
                            </div>
                        <?php } ?>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <form action="" method="POST" enctype="multipart/form-data">

                                        <div class="form-group">
                                            <label>Choose product</label>
                                            <?php echo form_dropdown('product_list_id', $getProList, set_value('product_list_id'), array('class' => 'form-control', 'id' => 'product_list_id')); ?>
                                            <div class="alert-warning alert-dismissible"><?php echo form_error('product_list_id'); ?></div>
                                        </div>
                                        <div class="form-group">
                                            <label>Choose Series</label>
                                            <?php echo form_dropdown('series_id', $getCouponSeriesList, set_value('series_id'), array('class' => 'form-control', 'id' => 'series_id')); ?>
                                            <div class="alert-warning alert-dismissible"><?php echo form_error('series_id'); ?></div>
                                        </div>

                                        <div class="form-group">
                                            <label>Coupon Quantity</label>
                                            <input type="number" name="coupon_quantity" class="form-control" value="<?php echo set_value('coupon_quantity'); ?>" placeholder="Coupon Quantity">
                                            <div class="alert-warning alert-dismissible"><?php echo form_error('coupon_quantity'); ?></div>
                                        </div>
                                        <div class="form-group">
                                            <label>Coupon Note</label>
                                            <input type="text" name="note" class="form-control" value="<?php echo set_value('note'); ?>" placeholder="Coupon Note">

                                        </div>

                                        <div class="form-group">
                                            <button class="btn btn-primary btn-success" name="submit" value="submit" type="submit">Submit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
