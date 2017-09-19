<?php
$this->load->view('admin/header');
$this->load->view('admin/navbar');
$this->load->view('admin/sidebar');
?>
<style>
    .auth_info_name .fa-user {
        color: #337ab7;
        display: inline-block;
        font-size: 14px;
        margin-left: 2px;
    }
    .auth_info_name .fa-user-secret {
        color: #f0ad4e;
        display: inline-block;
        font-size: 14px;
        margin-left: 2px;
    }
</style>
<!-- Page Content -->
<div id="page-wrapper">
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12"> 
                <div class="tracking-data-heading">
                    <div class="col-lg-4 pl-0">
                        <h3> User Management</h3>
                    </div>
                    <?php
                    if ($this->session->userdata('userRole') != Main::USER_ROLE_CRM) {
                        ?>
                        <div class="col-lg-3 pr-0 pull-right">
                            <div class="form-group form-group-0">
                                <form method="POST" action="" id="dateRangeForm"> 
                                    <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width:235px; position:absolute; right:0; left:inherit !important">
                                        <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
                                        <span></span> <b class="caret"></b>
                                    </div>
                                    <input type="hidden" name="start_date" id="start_date" value="<?php $this->input->get('start_date'); ?>">
                                    <input type="hidden" name="end_date" id="end_date" value="<?php $this->input->get('end_date'); ?>">
                                </form> 
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>   
            <div class="clearfix"></div>
        </div>

        <?php if ($this->session->flashdata('success_msg')) { ?>
            <div role="alert" class="alert alert-success alert-dismissible fade in">
                <button aria-label="Close" data-dismiss="alert" class="close" type="button"><span aria-hidden="true">×</span></button>
                <strong>Success!</strong><br>
                <?php echo $this->session->flashdata('success_msg'); ?>
            </div>
        <?php } ?>
        <?php
        if ($this->session->userdata('userRole') != Main::USER_ROLE_CRM) {
            ?>
            <div class="row">
                <?php echo managementPanel('user_management'); ?>
            </div>
            <?php
        }
        ?>
        <div class="row">
            <?php
            if ($this->session->userdata('userRole') != Main::USER_ROLE_CRM) {
                ?>
                <div class="col-lg-5">
                    <div class="form-group">
                        <select name="user_search" id="filterOption"  class="form-control">
                            <option value="">Select User Options</option>
                            <option value="highest_paid_subscribers" <?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'highest_paid_subscribers' ? 'selected' : '' ?>>Highest Paid subscribers </option>
                            <option value="lowest_paid_subscribers"  <?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'lowest_paid_subscribers' ? 'selected' : '' ?>>Lowest Paid subscribers </option>
                            <option value="maximum_bonds_added" <?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'maximum_bonds_added' ? 'selected' : '' ?>>Maximum Bonds added </option>
                            <option value="minimum_bonds_added" <?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'minimum_bonds_added' ? 'selected' : '' ?>>Minimum Bonds added </option>
                            <option value="maximum_bonds_overloaded" <?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'maximum_bonds_overloaded' ? 'selected' : '' ?>>Maximum Bonds Overloaded </option>
                            <option value="minimum_bonds_overloaded" <?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'minimum_bonds_overloaded' ? 'selected' : '' ?>>Minimum Bonds Overloaded </option>
                            <option value="highest_order_pending" <?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'highest_order_pending' ? 'selected' : '' ?>>Highest Order Pending </option>
                            <option value="lowest_order_pending"<?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'lowest_order_pending' ? 'selected' : '' ?>>Lowest Order Pending </option>
                            <option value="highest_order_failed"<?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'highest_order_failed' ? 'selected' : '' ?>>Highest Order Failed </option>
                            <option value="lowest_order_failed"<?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'lowest_order_failed' ? 'selected' : '' ?>>Lowest Order Failed </option>
                            <option value="highest_order_completed"<?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'highest_order_completed' ? 'selected' : '' ?>>Highest Order Completed </option>
                            <option value="lowest_order_completed"<?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'lowest_order_completed' ? 'selected' : '' ?>>Lowest Order Completed </option>

                            <option value="user_type_id_1" <?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'user_type_id_1' ? 'selected' : '' ?>>Show Customer Only </option>
                            <option value="subscriber" <?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'subscriber' ? 'selected' : '' ?>>Show Subscriber Only </option>
                            <option value="user_type_id_2" <?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'user_type_id_2' ? 'selected' : '' ?>>Show Developer Only </option>
                            <option value="user_type_id_3" <?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'user_type_id_3' ? 'selected' : '' ?>>Show CRM Officer Only </option>
                            <option value="status_1" <?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'status_1' ? 'selected' : '' ?>>Show Active User Only </option>
                            <option value="status_0" <?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'status_0' ? 'selected' : '' ?>>Show Inactive User Only </option>
                            <option value="status_2" <?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'status_2' ? 'selected' : '' ?>>Show Disabled User Only </option>
                            <option value="status_3" <?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'status_3' ? 'selected' : '' ?>>Show Suspended User Only </option>
                            <option value="flag_status_suspicious" <?php echo $this->input->get('user_search') && $this->input->get('user_search') == 'flag_status_suspicious' ? 'selected' : '' ?>>Show Suspicious User Only </option>
                        </select>
                    </div>
                </div>
                <?php
            }
            ?>
            <form method="GET" action="<?php echo base_url('admin/users/manage/'); ?>"> 

                <?php
                if ($this->input->get()) {
                    foreach ($this->input->get() as $key => $val) {
                        echo '<input type="hidden" name="' . $key . '" id="' . $key . '" value="' . $val . '">';
                    }
                }
                ?>


                <div class="col-lg-4">
                    <div class="form-group">
                        <input type="text" name="search_info" class="form-control" value="<?php echo $this->input->get('search_info'); ?>" placeholder="Name, Email, Phone, id, User id">
                    </div>
                </div>

                <div class="col-lg-1">
                    <button class="btn btn-success btn-mc" type="submit" name="search" value="1"> Search</button>

                </div>
                <?php
                if ($this->session->userdata('userRole') != Main::USER_ROLE_CRM) {
                    ?>
                    <div class="col-lg-2 pull-right">
                        <a class="btn btn-success" href="<?php echo base_url('export/csv/user_query'); ?>" target="_blank"><i class="fa fa-download" aria-hidden="true"></i> Download CSV</a>
                    </div>
                    <?php
                }
                ?>
            </form>
        </div>

    </div>

    <div class="col-sm-12 text-right"> 
        <div class="total-row">
            Total Users : <?php echo $total_rows; ?> (<?php echo $offset + 1; ?> to <?php echo ($offset + $limit); ?>)
        </div>  
    </div> 
    <div class="panel-body">
        <div class="dataTable_wrapper">
            <div id="dataTables-example_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
                <div class="row">
                    <div class="col-sm-12 text-right">
                        <div id="dataTables-example_filter" class="dataTables_filter"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="table-responsive" >
                        <table id="dataTables-example" class="table dataTable no-footer" role="grid" style="font-size: 12px;">
                            <thead>
                                <tr role="row">
                                    <th class="text-left" width="30%">User Info</th>
                                    <th class="text-left" width="20%">Order Summary</th>
                                    <th class="text-left" width="15%">Prizebond Info</th>
                                    <th class="text-left" width="25%">Activity Summary</th>
                                </tr>
                            </thead>
                            <?php
                            if (is_array($users_info) && !empty($users_info)) {
                                foreach ($users_info as $key => $single_user) {
                                    ?>

                                    <tbody>
                                        <tr>
                                            <?php echo miniUserProfile($single_user['id']); ?>
                                            <td class="text-left">

                                                Total : <?php echo!empty($single_user['total_order']) ? $single_user['total_order'] : 0; ?>  
                                                <br>Pending :<?php echo!empty($single_user['total_pending_order']) ? $single_user['total_pending_order'] : 0; ?>  
                                                <br>Completed : <?php echo!empty($single_user['total_completed_order']) ? $single_user['total_completed_order'] : 0; ?>  
                                                <br>Failed : <?php echo!empty($single_user['total_failed_order']) ? $single_user['total_failed_order'] : 0; ?>
                                            </td>
                                            <td class="text-left">
                                                Added : <?php echo!empty($single_user['allready_added_prizebond']) ? $single_user['allready_added_prizebond'] : 0; ?> of <?php echo!empty($single_user['rang_of_add_prizebond']) ? $single_user['rang_of_add_prizebond'] : 0; ?>
                                                <br/>Overloaded : <?php echo!empty($single_user['overloaded_prizebond']) ? $single_user['overloaded_prizebond'] : 0; ?>
                                            </td>
                                            <td class="text-left">
                                                <i class="fa fa-sign-in" aria-hidden="true"></i>
                                                <a href="javascript:void(0)" title=""><?php echo!empty($single_user['created_date_time']) ? date('d.m.y, h:i A', strtotime($single_user['created_date_time'])) : 'N/A'; ?></a>
                                                <br/><i class="fa fa-clock-o" aria-hidden="true"></i>
                                                <a href="javascript:void(0)" title=""><?php echo!empty($single_user['updated_date_time']) ? date('d.m.y, h:i A', strtotime($single_user['updated_date_time'])) : 'N/A'; ?></a>
                                            </td>

                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>

                                    <tr>
                                        <td class="text-center" colspan="4">
                                            Data not Found!!
                                        </td>
                                    </tr>
                                <?php }
                                ?>
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

    <!-- /.table-responsive -->
</div>


<script>
    $(function () {

<?php if (!empty($this->session->userdata('start_date')) && (!empty($this->session->userdata('end_date')))) { ?>
            $('#reportrange span').html('<?php echo date('M j,Y', strtotime($this->session->userdata('start_date'))); ?> - <?php echo date('M j,Y', strtotime($this->session->userdata('end_date'))); ?>');
                    $('#start_date').val('<?php echo $this->session->userdata('start_date'); ?>');
                    $('#end_date').val('<?php echo $this->session->userdata('end_date'); ?>');

<?php } ?>

                function cb(start, end) {
                    $('#reportrange span').html(start.format('MMM D, YYYY') + ' - ' + end.format('MMM D, YYYY'));
                    $('#start_date').val(start.format('YYYY-MM-DD'));
                    $('#end_date').val(end.format('YYYY-MM-DD'));

                }

                $('#reportrange').daterangepicker({
                    "opens": "left",
                    "applyClass": "btn-info",
                    "cancelClass": "btn-danger",
                    "startDate": moment('<?php echo $this->session->userdata('start_date'); ?>').format('YYYY-MM-DD'),
                    "endDate": moment('<?php echo $this->session->userdata('end_date'); ?>').format('YYYY-MM-DD'),
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                        'Lifetime': [moment('2017-01-1').format('YYYY-MM-DD'), moment()]
                    }
                }, cb);
                $('#reportrange').on('apply.daterangepicker', function (ev, picker) {
                    reloadPage();
                });

            });
</script>

<script>
    function reloadPage() {
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        $.ajax({
            type: "POST",
            url: "<?php echo base_url() . 'admin/admin/updateDateRange_ajax' ?>",
            data: {start_date: start_date, end_date: end_date},
            dataType: "json",
            success: function (response) {
                if (response.type == 'success') {
                    location.reload();
                }
            },
            error: function (xhr) {
                location.reload();
            }
        });
    }
</script>

<script type="text/javascript">
    $('#filterOption').change(function () {
        var baseurl = '<?php echo base_url(); ?>' + "admin/users/manage?user_search=" + $(this).val() + "&start_date=<?php echo $this->session->userdata('start_date') ?>&end_date=<?php echo $this->session->userdata('end_date') ?>";
        window.location = baseurl;
    });

</script>
<?php $this->load->view('admin/footer'); ?>