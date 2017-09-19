<?php

defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('dashboardGenerateAppInstalledByVersionCodeData')) {

    function dashboardGenerateAppInstalledByVersionCodeData($startDate, $endDate) {

        $CI = &get_instance();
        $sql = "SELECT app_version_code, COUNT(id) AS total FROM device_info WHERE added_date >= '2017-04-01' AND added_date <= '2017-04-30' GROUP BY app_version_code ORDER BY app_version_code DESC";

        $query = $CI->db->query($sql);
        foreach ($query->result() as $row) {
            $data['version'] = $row->app_version_code;
            $data['installed'] = $row->total;
            $jsonArray[] = $data;
        }
        $output = json_encode($jsonArray);
//
//        print"<pre>";
//        print_r($output);
//        print "</pre>";
//        die();
        
        

        $html = '<div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-bar-chart-o fa-fw"></i> App Installed/Version Code
                        <div class="pull-right">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                    Actions
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu pull-right" role="menu">
                                    <li><a href="#">Action</a>
                                    </li>
                                    <li><a href="#">Another action</a>
                                    </li>
                                    <li><a href="#">Something else here</a>
                                    </li>
                                    <li class="divider"></li>
                                    <li><a href="#">Separated link</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                     
                    <div class="panel-body">
                        <div id="morris-area-chart2"></div>
                    </div>
                     
                </div>';







        $html .= "
            <script>
            $(function() {

                    Morris.Area({
                        element: 'morris-area-chart2',
                        data: [{version:'n\/a',installed:'1'},{version:'43',installed:'7'}],
                        xkey: 'version',
                        ykeys: ['installed'],
                        labels: ['installed'],
                        pointSize: 2,
                        hideHover: 'auto',
                        resize: true
                    });

                });
            </script>";


        return $html;
    }

}

if (!function_exists('dashboardGenerateSalesCountByPaymentMethodType')) {

    function dashboardGenerateSalesCountByPaymentMethodType($startDate, $endDate) {

        $CI = &get_instance();
        $sql = "SELECT purchased_by,COUNT(id) AS total FROM subscription_product_purchase_list WHERE created_datetime >= '2017-04-01' AND created_datetime <= '2017-04-30' GROUP BY purchased_by ORDER BY purchased_by DESC";

        $html = '
                <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-bar-chart-o fa-fw"></i> SALE/Payment Method
                </div>
                <div class="panel-body">
                    <div id="morris-donut-chart2"></div>
                    <a href="#" class="btn btn-default btn-block">View Details</a>
                </div>
            </div>';

        $html .= "
            <script>
            $(function() {
                Morris.Donut({
                   element: 'morris-donut-chart2',
                   data: [{
                       label: 'Download Sales' ,
                       value: 12
                   }, {
                       label: 'In-Store Sales',
                       value: 30
                   }, {
                       label: 'Mail-Order Sales',
                       value: 20
                   }],
                   resize: true
               });
               });
            </script>";

        return $html;
    }

}

if (!function_exists('dashboardGenerateOrderHistory')) {

    function dashboardGenerateOrderHistory($startDate, $endDate) {

        $CI = &get_instance();
        $sql = "SELECT order_status,COUNT(id) AS total FROM subscription_order_list WHERE order_status='pending' OR order_status='completed' OR order_status='failed' AND ( order_created_date_time >= '2017-04-01' AND order_created_date_time <= '2017-04-30') GROUP BY order_status ORDER BY order_status DESC";

        $html = '<div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-bar-chart-o fa-fw"></i> Order History
                        <div class="pull-right">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                    Actions
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu pull-right" role="menu">
                                    <li><a href="#">Action</a>
                                    </li>
                                    <li><a href="#">Another action</a>
                                    </li>
                                    <li><a href="#">Something else here</a>
                                    </li>
                                    <li class="divider"></li>
                                    <li><a href="#">Separated link</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                     
                    <div class="panel-body">
                        <div id="morris-area-chart3"></div>
                    </div>
                     
                </div>';


        $html .= "
            <script>
            $(function() {

                    Morris.Area({
                        element: 'morris-area-chart3',
                        data: [{
                            period: '2010 Q1',
                            iphone: 2666,
                            ipad: null,
                            itouch: 2647
                        }, {
                            period: '2010 Q2',
                            iphone: 2778,
                            ipad: 2294,
                            itouch: 2441
                        }, {
                            period: '2010 Q3',
                            iphone: 4912,
                            ipad: 1969,
                            itouch: 2501
                        }, {
                            period: '2010 Q4',
                            iphone: 3767,
                            ipad: 3597,
                            itouch: 5689
                        }, {
                            period: '2011 Q1',
                            iphone: 6810,
                            ipad: 1914,
                            itouch: 2293
                        }, {
                            period: '2011 Q2',
                            iphone: 5670,
                            ipad: 4293,
                            itouch: 1881
                        }, {
                            period: '2011 Q3',
                            iphone: 4820,
                            ipad: 3795,
                            itouch: 1588
                        }, {
                            period: '2011 Q4',
                            iphone: 15073,
                            ipad: 5967,
                            itouch: 5175
                        }, {
                            period: '2012 Q1',
                            iphone: 10687,
                            ipad: 4460,
                            itouch: 2028
                        }, {
                            period: '2012 Q2',
                            iphone: 8432,
                            ipad: 5713,
                            itouch: 1791
                        }],
                        xkey: 'period',
                        ykeys: ['iphone', 'ipad', 'itouch'],
                        labels: ['iPhone', 'iPad', 'iPod Touch'],
                        pointSize: 2,
                        hideHover: 'auto',
                        resize: true
                    });

                });
            </script>";


        return $html;
    }

}



if (!function_exists('dashboardGenerateAppInstalledByDate')) {

    function dashboardGenerateAppInstalledByDate($startDate, $endDate) {

        $CI = &get_instance();

        $startDate = strtotime($startDate);
        $endDate = strtotime($endDate);
        $datediff = $endDate - $startDate;
        $numberOfDays = floor($datediff / (60 * 60 * 24));

        $labels = array();
        $sqls = array();

        if ($numberOfDays == 1) {
            $hours = 24 * $numberOfDays;
            $segments = floor($hours / 10);

//
            for ($i = $segments; $i >= 1; $i--) {
                $labels[] = date("h:i A", strtotime("-" . $i));
                $sqls[] = "SELECT COUNT(id) FROM device_info WHERE added_date >= DATE_ADD(NOW(), INTERVAL -$i HOUR) GROUP BY app_version_code";
            }
        } else if ($numberOfDays == 2) {
            
        }
    }

}

