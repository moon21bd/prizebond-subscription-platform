<?php
$this->load->view('admin/header');
$this->load->view('admin/navbar');
$this->load->view('admin/sidebar');
?>

<!-- Page Content -->
<div id="page-wrapper">
    <!--    <div class="container-fluid">-->
    <div class="row">
        <h3 class="page-header">Manage</h3>
        <div class="row">
            <div class="col-lg-6">
                <div class="clearfix">
                    <div class="btn-group pull-right" style="margin-bottom: 10px;">
                        <a class="btn btn-success" href="<?php echo site_url('admin/addResultYear'); ?>">
                            <i class="fa fa-plus">Add New</i>
                        </a>
                    </div>
                </div>

                <?php if ($this->session->flashdata('success_msg')) { ?>
                    <div role="alert" class="alert alert-success alert-dismissible fade in">
                        <button aria-label="Close" data-dismiss="alert" class="close" type="button"><span aria-hidden="true">×</span></button>
                        <strong>Success!</strong><br>
                        <?php echo $this->session->flashdata('success_msg'); ?>
                    </div>
                <?php } ?>

                <div class="panel panel-default">
                    <div class="panel-body">

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">

                                <thead>
                                    <tr>
                                        <th class="text-center">SL#</th>
                                        <th class="text-center">Draw Date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($showYear as $key => $show) { ?>
                                        <tr class="gradeU">
                                            <td class="text-center"><?php echo $key + 1; ?></td>
                                            <td class="text-center"><?php echo date('M j, Y', strtotime($show->year)); ?></td>
                                            <td class="text-center">
                                                <!--menu of list page-->
                                                <a href="<?php echo site_url('admin/editResultYear/' . $show->id); ?>" aria-hidden="true" title="Edit" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i>
                                                </a>
                                                <a href="<?php echo site_url('admin/deleteResultYear/' . $show->id); ?>" title="Delete" onclick="return confirm('Are you sure, you want delete this?');" class="btn btn-danger btn-xs"> <i class="fa fa-trash-o"></i>
                                                </a>
                                                
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                            <!--</div>-->
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- /.table-responsive -->
    </div>
</div>
<!-- /.panel-body -->

<?php $this->load->view('admin/footer'); ?>