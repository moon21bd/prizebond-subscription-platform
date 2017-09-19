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
                <h3 class="page-header"> Add Series</h3>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- /.row -->
        
        <?php if ($this->session->flashdata('success_msg')) { ?>
            <div role="alert" class="alert alert-warning alert-dismissible fade in">
                <button aria-label="Close" data-dismiss="alert" class="close" type="button"><span aria-hidden="true">×</span></button>
                <strong>Warning!</strong><br>
                <?php echo $this->session->flashdata('success_msg'); ?>
            </div>
        <?php } ?>

        <div class="row">
            <div class="col-lg-6">
                <form method="POST">
                    <div class="form-group">
                        <input class="form-control" name="series" type="text"/>
                    </div>
                    <div  class="form-group">
                        <button class="btn btn-success" type="submit" name="submit" value="1" class="btn btn-default">Add</button>
                    </div>
                </form>
            </div>
            <!-- /.col-lg-12 -->
        </div>



    </div>
</div>
<?php $this->load->view('admin/footer'); ?>