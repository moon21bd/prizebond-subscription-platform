<!DOCTYPE HTML>
<html>
    <head>
        <meta name="description" content="">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>::bkash selection::</title>
        <link href='http://fonts.googleapis.com/css?family=Roboto:500,900,700,500italic,300italic,400' rel='stylesheet' type='text/css'>
        <!-- Bootstrap -->
        <link href="<?php echo base_url('assets/subscriptions/css/tabcontent.css'); ?>" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url('assets/subscriptions/css/bootstrap.css'); ?>" rel="stylesheet">
        <!--slicknav-->
        <!-- main style -->
        <!-- main style -->
        <link href="<?php echo base_url('assets/subscriptions/css/style.css'); ?>" rel="stylesheet">
        <link href="<?php echo base_url('assets/subscriptions/css/responsive.css'); ?>" rel="stylesheet">
        <style>
            .number-area-list p {
                color: #e70157;
                float: left;
                font-size: 14px;
                margin-top: 6px;
                width: 100%;
                position: absolute;
                top: 28px;
                z-index: 999;
                text-align: left;
                left: 0;
            }
        </style>
    </head>
    <?php
    $this->load->helper('form');
    ?>
    <body>
        <header>
            <div class="container">

                <div class="row">
                    <div class="col-md-2 col-lg-2"></div>
                    <div class="col-xs-7 col-sm-4 col-md-3 col-lg-3">
                        <div class="logo">
                            <img src="<?php echo base_url('assets/subscriptions/images/logo.png'); ?>" alt="logo">
                        </div>
                    </div>
                    <div class="col-xs-1 col-sm-3 col-md-1 col-lg-2"></div>
                    <div class="col-xs-5 col-sm-5 col-md-4 col-lg-3">
                        <div class="security align-right">
                            <a href=""><img src="<?php echo base_url('assets/subscriptions/images/trust.png'); ?>" alt="Trust"></a>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-2 col-lg-2"></div>
                </div>
                <div class="row">
                    <div class="col-md-2"></div>
                    <div class="col-md-8 ">
                        <div class="banner">
                            <img src="<?php echo base_url('assets/subscriptions/images/banner.jpg'); ?>" alt="">
                        </div>
                    </div>
                    <div class="col-md-2"></div>
                </div>
            </div>
        </header>
        <!--/#header-->

        <div class="container">
            <div class="row">
                <div class="col-md-2 col-lg-2 hidden-xs"></div>
                <div class="col-xs-12 col-md-8 col-lg-8">
                    <form role="form" action="<?php //site_url('admin/page/create');  ?>" method="post" enctype="multipart/form-data"></form>

                    <div class="main_content">
                        <div class="col-xs-5 col-md-2 none-padding">
                            <div class="title">
                                <h1>bKash</h1>
                            </div>
                        </div>
                        <div class="col-md-7 hidden-xs"></div>
                        <div class="col-xs-7 col-md-3 on-padding">
                            <div class="language">
                                <ul class="tabs">
                                    <li><a href="#view1">Bangla</a></li>
                                    <li><a href="#view2">English</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <?php if ($this->session->flashdata('error')) { ?>
                        <div class="alert alert-danger text-center" >
                            <strong>Error ! </strong>&nbsp; <?php
                            $error = $this->session->flashdata('error');
                            echo $error;
                            ?>
                        </div>
                    <?php }
                    ?>

                    <form role="form" action="" method="post" enctype="multipart/form-data">
                        <div id="view1">
                            <div class="col-md-12 mob-no-padding">
                                <div class="payements">
                                    <h3>যেভাবে <span>বিকাশ অ্যাকাউন্টের</span> সাহায্যে পেমেন্ট করতে পারেন</h3>
                                    <h4>আপনার যদি বিকাশ অ্যাকাউন্ট থাকে তাহলে নিম্নোক্ত ধাপগুলো অনুসরণ করুন</h></h4>
                                </div>
                            </div>

                            <div class="col-md-12 mob-padding-half">
                                <div class="step">
                                    <ul>
                                        <li>
                                            <div class="sl-step">
                                                <p>ধাপ ১</p>
                                            </div>
                                            <div class="detalis-step">
                                                <p>ডায়াল *২৪৭#</p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="sl-step">
                                                <p>ধাপ ২</p>
                                            </div>
                                            <div class="detalis-step">
                                                <p>অপশন বেছে নিন: 'পেমেন্ট'</p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="sl-step">
                                                <p>ধাপ ৩</p>
                                            </div>
                                            <div class="detalis-step">
                                                <p>বিকাশ মার্চেন্ট অ্যাকাউন্ট লিখুন : 01700000000</p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="sl-step">
                                                <p>ধাপ ৪</p>
                                            </div>
                                            <div class="detalis-step">
                                                <p>টাকার পরিমাণ: <?php echo number_format((float) $this->session->userdata('price'), 2, '.', ''); ?></p>

                                            </div>
                                        </li>
                                        <li>
                                            <div class="sl-step">
                                                <p>ধাপ ৫</p>
                                            </div>
                                            <div class="detalis-step">
                                                <p>রেফারেন্স উল্লেখ করুন: <?php //echo $this->session->userdata('userId');                                                ?>  <?php echo $this->session->userdata('last_insert_id'); ?></p>
                                                <input type="hidden"  name="reference_no" value="<?php echo $this->session->userdata('last_insert_id'); ?>"/>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="sl-step">
                                                <p>ধাপ ৬</p>
                                            </div>
                                            <div class="detalis-step">
                                                <p>কাউন্টার নম্বর প্রবেশ করুন: <?php //echo $this->session->userdata('appId');                                                ?> 1</p>
                                                <input type="hidden"  name="counter_no" value="1<?php //echo $this->session->userdata('appId');                                                ?>"/>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="sl-step">
                                                <p>ধাপ ৭</p>
                                            </div>
                                            <div class="detalis-step">
                                                <p>লেনদেন নিশ্চিত করতে পিন প্রবেশ করুন: xxxx</p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="sl-step">
                                                <p>ধাপ ৮</p>
                                            </div>
                                            <div class="detalis-step">
                                                <p>লেনদেন সম্পূর্ণ করতে আপনার মোবাইল নম্বর এবং লেনদেন আইডি ব্যবহার করুন</p>
                                                <div class="number-area">
                                                    <p>মোবাইল নম্বর:</p>
                                                    <div class="input-group">
                                                        <div class="input-group-addon">+88</div>
                                                        <input type="text" required class="form-control" name="mobile" value="<?php echo set_value('mobile'); ?>" id="exampleInputAmount">
                                                        <!--<div class="alert-warning alert-dismissible number-area-list"><?php echo form_error('mobile'); ?></div>-->
                                                    </div>
                                                </div>
                                                <div class="number-area">
                                                    <p> ট্রানজেকশান  আইডি :</p>
                                                    <div class="form-group input-group">
                                                        <!--<span class="input-group-addon">@</span>-->
                                                        <div class="input-group-addon">TrxID</div>
                                                        <input type="text" required class="form-control extra-radious" name="transaction_id" value="<?php echo set_value('transaction_id'); ?>">
                                                        <!--<div class="alert-warning alert-dismissible number-area-list"><?php echo form_error('transaction_id'); ?></div>-->
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="button-area">
                                <!--<a href="<?php echo base_url(''); ?>" class="button"><< Back</a>-->
                                <button type="button" class="button" name="submit2" onclick="previous()"> << ফেরত</button>
                                <input type="submit" class="button" name="submit" value="পরবর্তী >>">
                            </div>
                        </div>
                    </form>

                    <form role="form" action="" method="post" enctype="multipart/form-data">
                        <div id="view2">
                            <div class="col-md-12">
                                <div class="payements">
                                    <h3>The way you can<span> Pay using your bKash </span>account</h3>
                                    <h4>If you have a bKash account then follow our procedure.</h4>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="step">
                                    <ul>
                                        <li>
                                            <div class="sl-step">
                                                <p>Step 1</p>
                                            </div>
                                            <div class="detalis-step">
                                                <p>Dialing *247#</p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="sl-step">
                                                <p>Step 2</p>
                                            </div>
                                            <div class="detalis-step">
                                                <p>Choose Option: 'Payment'</p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="sl-step">
                                                <p>Step 3</p>
                                            </div>
                                            <div class="detalis-step">
                                                <p>Enter Merchant Account : 01700000000</p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="sl-step">
                                                <p>Step 4</p>
                                            </div>
                                            <div class="detalis-step">
                                                <p>Amount: <?php echo number_format((float) $this->session->userdata('price'), 2, '.', ''); ?></p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="sl-step">
                                                <p>Step 5</p>
                                            </div>
                                            <div class="detalis-step">
                                                <p>Enter Reference No: <?php echo $this->session->userdata('userId'); ?></p>
                                                <input type="hidden"  name="reference_no" value="<?php echo $this->session->userdata('userId'); ?>"/>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="sl-step">
                                                <p>Step 6</p>
                                            </div>
                                            <div class="detalis-step">
                                                <p>Enter Counter No: <?php echo $this->session->userdata('appId'); ?></p>
                                                <input type="hidden"  name="counter_no" value="<?php echo $this->session->userdata('appId'); ?>"/>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="sl-step">
                                                <p>Step 7</p>
                                            </div>
                                            <div class="detalis-step">
                                                <p>Enter PIN to confirm the transaction: xxxx</p>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="sl-step">
                                                <p>Step 8</p>
                                            </div>
                                            <div class="detalis-step">
                                                <p>Enter your bKash Mobile Menu PIN</p>
                                                <div class="number-area">
                                                    <p>Mobile Number:</p>
                                                    <div class="input-group">
                                                        <div class="input-group-addon">+88</div>
                                                        <input type="text" class="form-control" name="mobile" value="<?php echo set_value('mobile'); ?>" id="exampleInputAmount">
                                                    </div>
                                                    <!--<div class="alert-warning alert-dismissible"><?php echo form_error('mobile'); ?></div>-->
                                                </div>
                                                <div class="number-area">
                                                    <p>Transaction Id:</p>
                                                    <div class="input-group">
                                                        <div class="input-group-addon">TrxID</div>
                                                        <input type="text"   class="form-control extra-radious" name="transaction_id" value="<?php echo set_value('transaction_id'); ?>" id="exampleInputAmount">
                                                        <!--<div class="alert-warning alert-dismissible"><?php echo form_error('transaction_id'); ?></div>-->
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="button-area">
                                    <button type="button" class="button" name="submit2" onclick="previous()"><< Back</button>
                                    <input type="submit" name="submit" value="Continue >>" class="button">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-2 col-lg-2"></div>
        </div>
        <script type="text/javascript">
            function previous()
            {
                var prize = '<?php echo $this->session->userdata('price') ?>';
                var product_id = '<?php echo $this->session->userdata('product_id') ?>';
                window.location = '<?php echo base_url() ?>' + '/subscriptions/payment/' + prize + '/' + product_id;

            }
        </script>
        <!-- Main jQuery  -->
        <script src="<?php echo base_url('assets/subscriptions/js/jquery.min.js'); ?>"></script>
        <!-- bootstrap.min.js -->
        <script src="<?php echo base_url('assets/subscriptions/js/bootstrap.min.js'); ?>"></script>
        <script src="http://cdnjs.cloudflare.com/ajax/libs/modernizr/2.6.2/modernizr.min.js"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
        <script src="<?php echo base_url('assets/subscriptions/js/tabcontent.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo base_url('assets/subscriptions/js/scripts.js'); ?>"></script>
    </body>
</html>