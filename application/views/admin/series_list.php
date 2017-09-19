<?php
$this->load->view('admin/header');
$this->load->view('admin/navbar');
$this->load->view('admin/sidebar');
?>
<!-- Page Content -->
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header">Series</h3>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- /.row -->
        
        <?php if ($this->session->flashdata('success_msg')) { ?>
            <div role="alert" class="alert alert-success alert-dismissible fade in">
                <button aria-label="Close" data-dismiss="alert" class="close" type="button"><span aria-hidden="true">×</span></button>
                <strong>Success!</strong><br>
                <?php echo $this->session->flashdata('success_msg'); ?>
            </div>
        <?php } ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="form-group">
                    <div class="pull-right" style="margin: 15px;">
                        <a href="<?php echo site_url('admin/addSeries'); ?>"><button class="btn btn-success"><i class="fa fa-plus"></i>  Add Series</button></a>
                    </div>

                    <textarea class="form-control" disabled rows="3"><?php echo trim($series); ?></textarea>
                </div>
            </div>
            <!-- /.col-lg-12 -->
        </div>

    </div>
</div>
<?php $this->load->view('admin/footer'); ?>