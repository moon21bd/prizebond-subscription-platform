<?php
$this->load->view('admin/dashboard_header');
$this->load->view('admin/navbar');
$this->load->view('admin/sidebar');
?>
<!--dashboard content start-->
<!--<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.bundle.min.js"></script>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <div class="tracking-data-heading">
                <div class="col-lg-4 pl-0">
                    <h3>Dashboard</h3>
                </div>
                <div class="col-lg-3 pr-0 pull-right">
                    <div class="form-group form-group-0">
                        <form method="POST" action="" id="dateRangeForm"> 
                            <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;  position:absolute; right:0; left:inherit !important; text-align:  center;">
                                <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
                                <span></span> <b class="caret"></b>
                            </div>
                            <input type="hidden" name="start_date" id="start_date" value="<?php $this->input->get('start_date'); ?>">
                            <input type="hidden" name="end_date" id="end_date" value="<?php $this->input->get('end_date'); ?>">
                        </form> 
                    </div>
                </div>

            </div>
        </div>
        <!-- /.col-lg-12 -->
    </div>
    <!-- /.row -->
    <?php echo managementPanel('dashboard'); ?>

    <!-- /.row -->
    <div class="row">
        <div class="col-lg-8">
            <div class="panel panel-default contant-preloader" style="min-height:422px; max-height:422px;">
                <div class="panel-heading">
                    <i class="fa fa-bar-chart-o fa-fw"></i> App Installed/ Version Code

                </div>
                <div class="panel-body">
                    <canvas id="canvas_installed"></canvas>
                </div>

            </div>

        </div>

        <div class="col-lg-4">
            <div class="panel panel-default contant-preloader" style="min-height:422px; max-height:422px;">
                <div class="panel-heading">
                    <i class="fa fa-dot-circle-o fa-fw"></i> Sales / Payment method
                    <div class="pull-right">
                        Total Sales: <span id="total_sales">0</span>
                    </div>

                </div>
                <div class="panel-body">
                    <canvas id="canvas_sales"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel panel-default contant-preloader" style="min-height:424px; max-height:424px;">
                <div class="panel-heading">
                    <i class="fa fa-bar-chart-o fa-fw"></i> Order History
                </div>
                <div class="panel-body">
                    <canvas id="canvas_orders"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="panel panel-default contant-preloader" style="min-height:395px; max-height:395px;">
                <div class="panel-heading">
                    <i class="fa fa-dot-circle-o fa-fw"></i> Top Devices
                    <div class="pull-right">
                        Total Devices: <span id="total_devices">0</span>
                    </div>
                </div>
                <div class="panel-body">
                    <canvas id="canvas_devices"></canvas>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8">
                <div class="panel panel-default contant-preloader" style="min-height:434px; max-height:434px;">
                    <div class="panel-heading">
                        <i class="fa fa-bar-chart-o fa-fw"></i> Users Activity
                    </div>
                    <div class="panel-body">
                        <canvas id="canvas_activity"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">

            </div>
        </div>



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
                            'Lifetime': [moment('2017-01-01').format('YYYY-MM-DD'), moment()]
                        }
                    }, cb);
                    $('#reportrange').on('apply.daterangepicker', function (ev, picker) {
                        reloadPage();
                    });
                });</script>
    <script>
        function phpcb() {
            $('#reportrange span').html('<?php echo date('M j,Y', strtotime($this->input->get('start_date'))); ?> - <?php echo date('M j,Y', strtotime($this->input->get('end_date'))); ?>');
                    $('#start_date').val('<?php echo $this->input->get('start_date'); ?>');
                    $('#end_date').val('<?php echo $this->input->get('end_date'); ?>');
                }
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


    <script>
        window.chartColors = {
            red: 'rgb(255, 99, 132)',
            orange: 'rgb(255, 159, 64)',
            yellow: 'rgb(255, 205, 86)',
            green: 'rgb(75, 192, 192)',
            blue: 'rgb(54, 162, 235)',
            purple: 'rgb(153, 102, 255)',
            grey: 'rgb(201, 203, 207)',
            lightBlue: 'rgb(204, 230, 255)',
            lightGreen: 'rgb(155, 224, 195, 0.2)',
            lightPurple: 'rgb(153, 102, 255, 0.2)'
        };
        function transparentize(color, opacity) {
            var alpha = opacity === undefined ? 0.5 : 1 - opacity;
            return Chart.helpers.color(color).alpha(alpha).rgbString();
        }

        var presets = window.chartColors;
        var config_orders = {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                        label: "Failed",
                        backgroundColor: window.chartColors.red,
                        borderColor: window.chartColors.red,
                        data: [],
                        fill: false,
                    }, {
                        label: "Success",
                        fill: false,
                        backgroundColor: window.chartColors.blue,
                        borderColor: window.chartColors.blue,
                        data: [],
                    }, {
                        label: "Pending",
                        fill: false,
                        backgroundColor: window.chartColors.orange,
                        borderColor: window.chartColors.orange,
                        data: [],
                    }]
            },
            options: {
                responsive: true,
                //            title: {
                //                display: true,
                //                text: 'Order History'
                //            },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    xAxes: [{
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'Date'
                            }
                        }],
                    yAxes: [{
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'Value'
                            }
                        }]
                }
            }
        };
        var config_installed = {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                        label: "App Installed",
                        backgroundColor: window.chartColors.lightBlue,
                        borderColor: window.chartColors.blue,
                        data: [],
                    }]
            },
            options: {
                responsive: true,
                //            title: {
                //                display: true,
                //                text: 'App Installed/ Version Code'
                //            },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    xAxes: [{
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'Version Code'
                            }
                        }],
                    yAxes: [{
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'App Installed'
                            }
                        }]
                }
            }
        };
        //    var config_registrations = {
        //        type: 'line',
        //        data: {
        //            labels: [],
        //            datasets: [{
        //                    label: "Registared",
        //                    backgroundColor: window.chartColors.lightGreen,
        //                    borderColor: window.chartColors.green,
        //                    data: [],
        //                }]
        //        },
        //        options: {
        //            responsive: true,
        ////            title: {
        ////                display: true,
        ////                text: 'App Installed/ Version Code'
        ////            },
        //            tooltips: {
        //                mode: 'index',
        //                intersect: false,
        //            },
        //            hover: {
        //                mode: 'nearest',
        //                intersect: true
        //            },
        //            scales: {
        //                xAxes: [{
        //                        display: true,
        //                        scaleLabel: {
        //                            display: true,
        //                            labelString: 'Date'
        //                        }
        //                    }],
        //                yAxes: [{
        //                        display: true,
        //                        scaleLabel: {
        //                            display: true,
        //                            labelString: 'Registared'
        //                        }
        //                    }]
        //            }
        //        }
        //    };
        //    var config_bonds = {
        //        type: 'line',
        //        data: {
        //            labels: [],
        //            datasets: [{
        //                    label: "Prize Bonds",
        //                    backgroundColor: window.chartColors.lightPurple,
        //                    borderColor: window.chartColors.purple,
        //                    data: [],
        //                }]
        //        },
        //        options: {
        //            responsive: true,
        //            tooltips: {
        //                mode: 'index',
        //                intersect: false,
        //            },
        //            hover: {
        //                mode: 'nearest',
        //                intersect: true
        //            },
        //            scales: {
        //                xAxes: [{
        //                        display: true,
        //                        scaleLabel: {
        //                            display: true,
        //                            labelString: 'Date'
        //                        }
        //                    }],
        //                yAxes: [{
        //                        display: true,
        //                        scaleLabel: {
        //                            display: true,
        //                            labelString: 'Bond Added'
        //                        }
        //                    }]
        //            }
        //        }
        //    };
        var config_sales = {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [
                    {
                        data: [],
                        backgroundColor: [
                            window.chartColors.orange,
                            window.chartColors.blue,
                            window.chartColors.green,
                            window.chartColors.lightBlue,
                            window.chartColors.yellow,
                            window.chartColors.purple,
                            window.chartColors.red,
                        ]

                    }]
            },
            options: {
                animation: {
                    animateScale: true
                }

            }
        };
        var config_devices = {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [
                    {
                        backgroundColor: [
                            window.chartColors.red,
                            window.chartColors.lightBlue,
                            window.chartColors.orange,
                            window.chartColors.purple,
                            window.chartColors.yellow,
                            window.chartColors.green,
                            window.chartColors.red,
                            window.chartColors.blue,
                            window.chartColors.purple,
                            window.chartColors.orange,
                        ],
                        borderWidth: 1,
                        data: [],
                    }
                ]
            },
            options: {
                animation: {
                    animateScale: true
                }

            }
        };
        var config_activity = {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                        label: "Activity",
                        backgroundColor: window.chartColors.red,
                        borderColor: window.chartColors.red,
                        data: [],
                        fill: false,
                    }, {
                        label: "Registration",
                        fill: false,
                        backgroundColor: window.chartColors.blue,
                        borderColor: window.chartColors.blue,
                        data: [],
                    }, {
                        label: "Verification",
                        fill: false,
                        backgroundColor: window.chartColors.orange,
                        borderColor: window.chartColors.orange,
                        data: [],
                    }, {
                        label: "Bonds Added",
                        fill: false,
                        backgroundColor: window.chartColors.green,
                        borderColor: window.chartColors.green,
                        data: [],
                    }]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: 'Users Activity'
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    xAxes: [{
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'Date'
                            }
                        }],
                    yAxes: [{
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'Value'
                            }
                        }]
                }
            }
        };
        window.onload = function () {

            var ctx = document.getElementById("canvas_installed").getContext("2d");
            window.installedLine = new Chart(ctx, config_installed);

            var ctx2 = document.getElementById("canvas_orders").getContext("2d");
            window.ordersLine = new Chart(ctx2, config_orders);

            var ctx3 = document.getElementById("canvas_sales").getContext("2d");
            window.salesDoughnut = new Chart(ctx3, config_sales);

            //        var ctx4 = document.getElementById("canvas_registrations").getContext("2d");
            //        window.registrationLine = new Chart(ctx4, config_registrations);
            //
            //        var ctx5 = document.getElementById("canvas_bonds").getContext("2d");
            //        window.bondAddedLine = new Chart(ctx5, config_bonds);

            var ctx6 = document.getElementById("canvas_devices").getContext("2d");
            window.devicesDoughnut = new Chart(ctx6, config_devices);

            var ctx7 = document.getElementById("canvas_activity").getContext("2d");
            window.activityLine = new Chart(ctx7, config_activity);


            getGraphDataFromServer();

        }


        function getGraphDataFromServer() {
            $.ajax({
                type: "GET",
                url: "<?php echo base_url() . 'admin/admin/dashboard_ajax' ?>",
                dataType: "json",
                success: function (data) {

                    if (data.hasOwnProperty('orders')) {
                        //document.getElementById("orders").innerHTML += JSON.stringify(eventDataJSON) + "<br>";
                        processOrdersData(data.orders);
                    }
                    if (data.hasOwnProperty('apps')) {
                        //document.getElementById("orders").innerHTML += JSON.stringify(eventDataJSON) + "<br>";
                        processAppsData(data.apps);
                    }
                    if (data.hasOwnProperty('sales')) {
                        document.getElementById("total_sales").innerHTML = data.sales.total;
                        //alert(data.sales.total);
                        processSalesData(data.sales);
                    }
                    if (data.hasOwnProperty('registrations')) {
                        //document.getElementById("orders").innerHTML += JSON.stringify(eventDataJSON) + "<br>";
                        processRegistrationData(data.registrations);
                    }
                    if (data.hasOwnProperty('bonds')) {
                        //document.getElementById("orders").innerHTML += JSON.stringify(eventDataJSON) + "<br>";
                        processBondAddedData(data.bonds);
                    }
                    if (data.hasOwnProperty('devices')) {
                        document.getElementById("total_devices").innerHTML = data.devices.total;
                        //document.getElementById("orders").innerHTML += JSON.stringify(eventDataJSON) + "<br>";
                        processDevicesData(data.devices);
                    }
                    if (data.hasOwnProperty('usersData')) {
                        //document.getElementById("orders").innerHTML += JSON.stringify(eventDataJSON) + "<br>";
                        processUsersActivity(data.usersData);
                    }
                }

            });
        }


        function processOrdersData(orders) {

            config_orders.data.labels = orders.labels;
            config_orders.data.datasets.forEach(function (dataset) {

                if (dataset.label == "Pending") {
                    dataset.data = orders.data.pending;
                } else if (dataset.label == "Success") {
                    dataset.data = orders.data.completed;
                } else if (dataset.label == "Failed") {
                    dataset.data = orders.data.failed;
                }
            });

            window.ordersLine.update();


        }
        function processRegistrationData(registrations) {

            config_registrations.data.labels = registrations.labels;
            config_registrations.data.datasets.forEach(function (dataset) {

                dataset.data = registrations.data;

            });

            window.registrationLine.update();
        }

        function processBondAddedData(bonds) {

            config_bonds.data.labels = bonds.labels;
            config_bonds.data.datasets.forEach(function (dataset) {

                dataset.data = bonds.data;

            });

            window.bondAddedLine.update();
        }

        function processAppsData(apps) {
            //consol.log(JSON.stringify(apps));
            config_installed.data.labels = apps.labels;

            config_installed.data.datasets.forEach(function (dataset) {
                dataset.data = apps.data;
            });

            window.installedLine.update();
        }

        function processSalesData(sales) {

            config_sales.data.labels = sales.labels;
            config_sales.data.datasets.forEach(function (dataset) {
                dataset.data = sales.data;
            });

            window.salesDoughnut.update();
        }
        function processDevicesData(devices) {

            config_devices.data.labels = devices.labels;
            config_devices.data.datasets.forEach(function (dataset) {
                dataset.data = devices.data;
            });

            window.devicesDoughnut.update();
        }
        function processUsersActivity(usersData) {

            config_activity.data.labels = usersData.labels;

            config_activity.data.datasets.forEach(function (dataset) {

                if (dataset.label == "Activity") {
                    dataset.data = usersData.data.activity;
                } else if (dataset.label == "Registration") {
                    dataset.data = usersData.data.registration;
                } else if (dataset.label == "Verification") {
                    dataset.data = usersData.data.verification;
                } else if (dataset.label == "Bonds Added") {
                    dataset.data = usersData.data.bonds_added;
                }

            });

            window.activityLine.update();

        }



    </script>


    <?php $this->load->view('admin/dashboard_footer'); ?>