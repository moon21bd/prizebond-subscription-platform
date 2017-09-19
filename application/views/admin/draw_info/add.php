
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <h3 class="page-header">Add New Draw Information</h3>
        </div>

        <?php if ($this->session->flashdata('success_msg')) { ?>
            <div role="alert" class="alert alert-success alert-dismissible fade in">
                <button aria-label="Close" data-dismiss="alert" class="close" type="button"><span aria-hidden="true">×</span></button>
                <strong>Success!</strong><br>
                <?php echo $this->session->flashdata('success_msg'); ?>
            </div>
        <?php } ?>
        <?php if ($this->session->flashdata('warning_msg')) { ?>
            <div role="alert" class="alert alert-warning alert-dismissible fade in">
                <button aria-label="Close" data-dismiss="alert" class="close" type="button"><span aria-hidden="true">×</span></button>
                <strong>Warning!</strong><br>
                <?php echo $this->session->flashdata('warning_msg'); ?>
            </div>
        <?php } ?>
        <!-- /.row -->
        <div class="row drow-info-container">
            <form action="" method="POST">
                <div class="login-wrap">
                    <section>
                        <h3 class="page-inner-title">Basic Info</h3>
                        <div class="row">
                            <div class="form-group col-xs-6">
                                <label>Result Date</label>
                                <input class="form-control" type="text" name="result_date"/>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-xs-6">
                                <label>Result Series</label>
                                <textarea rows="3" cols="50" name="result_series" placeholder="কক,কখ,কগ,কঘ,কঙ,কচ,কছ,কজ,কঝ . . ." class="form-control"></textarea>
                                <div class="alert-danger alert-dismissible"><?php echo form_error('result_series'); ?></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-xs-3">
                                <label>Current Draw Number</label>
                                <input type="text" name="result_number" placeholder="85" class="form-control" value="<?php echo set_value('result_number'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('result_number'); ?></div>
                            </div>
                            <div class="form-group col-xs-3">
                                <label> Current Bond Value</label>
                                <input type="text" name="bond_value" placeholder="100" class="form-control" value="<?php echo set_value('bond_value'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('bond_value'); ?></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-xs-3">
                                <label>Total Series</label>
                                <input type="text" name="total_series_number" placeholder="44" class="form-control" value="<?php echo set_value('total_series_number'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('total_series_number'); ?></div>
                            </div>
                            <div class="form-group col-xs-3"></div>
                        </div>
                    </section>
                    <section>
                        <h3 class="page-inner-title">Prizes</h3>
                        <div class="row">
                            <div class="form-group col-xs-3">
                                <label>No. of Prizes(1st)->1</label>
                                <input type="text" name="first_prize_number" placeholder="1" class="form-control" value="<?php echo set_value('first_prize_number'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('first_prize_number'); ?></div>
                            </div>
                            <div class="form-group col-xs-3">
                                <label>No. of Prizes(2nd)->1</label>
                                <input type="text" name="second_prize_number" placeholder="1" class="form-control" value="<?php echo set_value('second_prize_number'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('second_prize_number'); ?></div>
                            </div>
                            <div class="form-group col-xs-3">
                                <label>No. of Prizes(3rd)->2</label>
                                <input type="text" name="third_prize_number" placeholder="2" class="form-control" value="<?php echo set_value('third_prize_number'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('third_prize_number'); ?></div>
                            </div>

                            <div class="form-group col-xs-3">
                                <label>No. of Prizes(4th)->2</label>
                                <input type="text" name="fourth_prize_number" placeholder="2" class="form-control" value="<?php echo set_value('fourth_prize_number'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('fourth_prize_number'); ?></div>
                            </div>

                            <div class="form-group col-xs-3">
                                <label>No. of Prizes(5th)->40</label>
                                <input type="text" name="fifth_prize_number" placeholder="40" class="form-control" value="<?php echo set_value('fifth_prize_number'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('fifth_prize_number'); ?></div>
                            </div>
                            <div class="form-group col-xs-3">
                                <label>Total Prizes (1st)->44</label>
                                <input type="text" name="total_first_prize_number" placeholder="44" class="form-control" value="<?php echo set_value('total_first_prize_number'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('total_first_prize_number'); ?></div>
                            </div>
                            <div class="form-group col-xs-3">
                                <label>Total Prizes (2nd)->44</label>
                                <input type="text" name="total_second_prize_number" placeholder="44" class="form-control" value="<?php echo set_value('total_second_prize_number'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('total_second_prize_number'); ?></div>
                            </div>

                            <div class="form-group col-xs-3">
                                <label>Total Prizes (3rd)->88</label>
                                <input type="text" name="total_third_prize_number" placeholder="88" class="form-control" value="<?php echo set_value('total_third_prize_number'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('total_third_prize_number'); ?></div>
                            </div>

                            <div class="form-group col-xs-3">
                                <label>Total Prizes (4th)->88</label>
                                <input type="text" name="total_fourth_prize_number" placeholder="88" class="form-control" value="<?php echo set_value('total_fourth_prize_number'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('total_fourth_prize_number'); ?></div>
                            </div>
                            <div class="form-group col-xs-3">
                                <label>Total Prizes (5th)->1760</label>
                                <input type="text" name="total_fifth_prize_number" placeholder="1760" class="form-control" value="<?php echo set_value('total_fifth_prize_number'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('total_fifth_prize_number'); ?></div>
                            </div>
                            <div class="form-group col-xs-3">
                                <label>Total Prizes->2024</label>
                                <input type="text" name="total_prize_number" placeholder="2024" class="form-control" value="<?php echo set_value('total_prize_number'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('total_prize_number'); ?></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-xs-6">
                                <label>First Prize Value Tk</label>
                                <input type="text" name="first_prize_value_tk" placeholder="600000" class="form-control" value="<?php echo set_value('first_prize_value_tk'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('first_prize_value_tk'); ?></div>
                            </div>
                            <div class="form-group col-xs-6">
                                <label>Second Prize Value Tk</label>
                                <input type="text" name="second_prize_value_tk" placeholder="325000" class="form-control" value="<?php echo set_value('second_prize_value_tk'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('second_prize_value_tk'); ?></div>
                            </div>
                            <div class="form-group col-xs-6">
                                <label>Third Prize Value Tk</label>
                                <input type="text" name="third_prize_value_tk" placeholder="100000" class="form-control" value="<?php echo set_value('third_prize_value_tk'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('third_prize_value_tk'); ?></div>
                            </div>
                            <div class="form-group col-xs-6">
                                <label>Fourth Prize Value Tk</label>
                                <input type="text" name="fourth_prize_value_tk" placeholder="50000" class="form-control" value="<?php echo set_value('fourth_prize_value_tk'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('fourth_prize_value_tk'); ?></div>
                            </div>
                            <div class="form-group col-xs-6">
                                <label>Fifth Prize Value Tk</label>
                                <input type="text" name="fifth_prize_value_tk" placeholder="10000" class="form-control" value="<?php echo set_value('fifth_prize_value_tk'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('fifth_prize_value_tk'); ?></div>
                            </div>
                        </div>
                    </section>
                    <section>
                        <h3 class="page-inner-title">Bond Info</h3>
                        <div class="row">
                            <div class="form-group col-xs-6">
                                <label>First Prize bond Number</label>
                                <input type="text" name="first_prize_bond_number" placeholder="0993630" class="form-control" value="<?php echo set_value('first_prize_bond_number'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('first_prize_bond_number'); ?></div>
                            </div>
                            <div class="form-group col-xs-6">
                                <label>Second Prize bond Number</label>
                                <input type="text" name="second_prize_bond_number" placeholder="0479212" class="form-control" value="<?php echo set_value('second_prize_bond_number'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('second_prize_bond_number'); ?></div>
                            </div>
                            <div class="form-group col-xs-6">
                                <label>Third Prize bond Number</label>
                                <input type="text" name="third_prize_bond_number" placeholder="0381278,0599790" class="form-control" value="<?php echo set_value('third_prize_bond_number'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('third_prize_bond_number'); ?></div>
                            </div>
                            <div class="form-group col-xs-6">
                                <label>Fourth Prize bond Number</label>
                                <input type="text" name="fourth_prize_bond_number" placeholder="0136980,0890738" class="form-control" value="<?php echo set_value('fourth_prize_bond_number'); ?>">
                                <div class="alert-danger alert-dismissible"><?php echo form_error('fourth_prize_bond_number'); ?></div>
                            </div>
                            <div class="form-group col-xs-6">
                                <label>Fifth Prize bond Number</label>
                                <textarea rows="8" cols="50" name="fifth_prize_bond_number" placeholder="0012389 0198935 0423937 0698789. . . ." class="form-control" value="<?php echo set_value('fifth_prize_bond_number'); ?>"></textarea>
                                <div class="alert-danger alert-dismissible"><?php echo form_error('fifth_prize_bond_number'); ?></div>
                            </div>
                        </div>
                    </section>
                    <div class="form-group">
                        <button class="btn btn-success col-md-1" type="submit" name="add" value="1" class="btn btn-default">Add</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- /.container-fluid -->
</div>

<script type="text/javascript">
    $('input[name="result_date"]').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        locale: {
            format: 'YYYY-MM-DD'
        }
    });
</script>
