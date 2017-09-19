<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>::Subscription::</title>
        <!-- Bootstrap -->
        <link href="<?php echo base_url(); ?>assets/css/bootstrap.css" rel="stylesheet">
        <link href="<?php echo base_url(); ?>assets/css/prizebond.css" rel="stylesheet">
        <link href="<?php echo base_url(); ?>assets/css/preset.css" rel="stylesheet">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
                      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
                      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
                    <![endif]-->
    </head>
    <body>
        <div class="subscription_wrapper">
            <ul class="subscription_list">

                <?php
                if (is_array($productList) && (count($productList))) {
                    foreach ($productList as $key => $productInfo) {
                        ?>
                        <li>
                            <div class="bond_info">
                                <h3 class="packeg_number"><?php echo!empty($productInfo['name']) ? $productInfo['name'] : 'N/A'; ?></h3>
                                <div class="date_time_wrapper">
                                    <p class="event_date_time"><?php echo $productInfo['valid_start_datetime']; ?></p>
                                    <span class="start_time"> শুরু তারিখ </span> 
                                </div>
                                <div class="date_time_wrapper">
                                    <p class="event_date_time"><?php echo $productInfo['valid_end_datetime']; ?></p>
                                    <span class="start_time"> শেষ তারিখ </span> 
                                </div>
                            </div>
                            <div class="bond_prize_category">
                                <div class="bond_prize_type">
                                    <?php if ($productInfo['purchased_by'] == 'coupon') { ?>
                                        <img src="<?php echo base_url(); ?>images/cupon.png" alt="" title="" />
                                    <?php } elseif($productInfo['purchased_by'] == 'bKash') { ?>
                                        <img src="<?php echo base_url(); ?>images/bkash.png" alt="" title="" />
                                    <?php }
                                    ?>
                                </div>
                                <div class="bond_prize_ammount">
                                    <?php if ($productInfo['price'] == 'N/A') { ?>
                                        &#2547; Free
                                    <?php } else { ?>
                                        &#2547;  <?php echo!empty($productInfo['price']) ? $productInfo['price'] : ' ০০'; ?>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </li>
                        <?php
                    }
                } else {
                    ?><li class="text-center"> Data Not Found</li>
                    <?php } ?>

            </ul>
        </div>
    </body>
</html>