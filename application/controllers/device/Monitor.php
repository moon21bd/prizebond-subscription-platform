<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require_once APPPATH . 'controllers/Main.php';

class Monitor extends Main {

    function __construct() {
        parent::__construct();
        $this->load->model('Devicelist_model');
        $this->load->model('admin_model');
        $this->load->helper('global_helper');
        $this->load->helper('user_profile_info_helper');

        if ($this->checkHost()) {
            $this->checkAuth();
        }
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
    }

    public function index($offset = 0) {

        // load the view file
        $data['title'] = 'Device List';

        $data['error'] = '';
        $data['offset'] = $offset;

        $totalRow = $this->Devicelist_model->countTotalDevice('device_info');
        $perPage = 5;
        $uri_segment = 4;

        generatePagging('device/monitor/index', $totalRow, $perPage, $uri_segment, 3);
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $twodayago = date('Y-m-d', strtotime('-2 days'));
        $threedayago = date('Y-m-d', strtotime('-3 days'));
        $fourdayago = date('Y-m-d', strtotime('-4 days'));
        $fivedayago = date('Y-m-d', strtotime('-5 days'));
        //  $sixdayago = date('Y-m-d', strtotime('-6 days'));
        $data['today'] = $this->Devicelist_model->countInstalledAppByAddedDate($today, 2);
        $data['todayiOS'] = $this->Devicelist_model->countInstalledAppByAddedDate($today, 1);

        $data['yesterday'] = $this->Devicelist_model->countInstalledAppByAddedDate($yesterday, 2);
        $data['yesterdayiOS'] = $this->Devicelist_model->countInstalledAppByAddedDate($yesterday, 1);

        $data['twodayago'] = $this->Devicelist_model->countInstalledAppByAddedDate($twodayago, 2);
        $data['twodayagoiOS'] = $this->Devicelist_model->countInstalledAppByAddedDate($twodayago, 1);

        $data['threedayago'] = $this->Devicelist_model->countInstalledAppByAddedDate($threedayago, 2);
        $data['threedayagoiOS'] = $this->Devicelist_model->countInstalledAppByAddedDate($threedayago, 1);

        $data['fourdayago'] = $this->Devicelist_model->countInstalledAppByAddedDate($fourdayago, 2);
        $data['fourdayagoiOS'] = $this->Devicelist_model->countInstalledAppByAddedDate($fourdayago, 1);

        $data['fivedayago'] = $this->Devicelist_model->countInstalledAppByAddedDate($fivedayago, 2);
        $data['fivedayagoiOS'] = $this->Devicelist_model->countInstalledAppByAddedDate($fivedayago, 1);

        $data['todayActive'] = $this->Devicelist_model->countActiveAppByAddedDate($today, 2);
        $data['todayActiveiOS'] = $this->Devicelist_model->countActiveAppByAddedDate($today, 1);

        $data['yesterdayActive'] = $this->Devicelist_model->countActiveAppByAddedDate($yesterday, 2);
        $data['yesterdayActiveiOS'] = $this->Devicelist_model->countActiveAppByAddedDate($yesterday, 1);

        $data['twodayagoActive'] = $this->Devicelist_model->countActiveAppByAddedDate($twodayago, 2);
        $data['twodayagoActiveiOS'] = $this->Devicelist_model->countActiveAppByAddedDate($twodayago, 1);

        $data['threedayagoActive'] = $this->Devicelist_model->countActiveAppByAddedDate($threedayago, 2);
        $data['threedayagoActiveiOS'] = $this->Devicelist_model->countActiveAppByAddedDate($threedayago, 1);

        $data['fourdayagoActive'] = $this->Devicelist_model->countActiveAppByAddedDate($fourdayago, 2);
        $data['fourdayagoActiveiOS'] = $this->Devicelist_model->countActiveAppByAddedDate($fourdayago, 1);

        $data['fivedayagoActive'] = $this->Devicelist_model->countActiveAppByAddedDate($fivedayago, 2);
        $data['fivedayagoActiveiOS'] = $this->Devicelist_model->countActiveAppByAddedDate($fivedayago, 1);

        $data['android'] = $this->Devicelist_model->getActiveDevice(2);
        $data['iOS'] = $this->Devicelist_model->getActiveDevice(1);

        $data['activePushDevice'] = $this->Devicelist_model->getTotalActivePushDevice(2);
        $data['activePushDeviceiOS'] = $this->Devicelist_model->getTotalActivePushDevice(1);

        $data['manualPushOff'] = $this->Devicelist_model->getTotalManualPushOffDevice(2);
        $data['manualPushOffiOS'] = $this->Devicelist_model->getTotalManualPushOffDevice(1);

        $data['versionInfo'] = $this->Devicelist_model->getVersionInfo();

        $data['unknownVersionInfo'] = $this->Devicelist_model->countDeviceByUnknownAppVersionName(2);
        $data['unknownVersionInfoiOS'] = $this->Devicelist_model->countDeviceByUnknownAppVersionName(1);

        if ($this->input->post()) {
            $data['allDevice'] = $this->Devicelist_model->getSearchData(trim($this->input->post('searchData')));
            $data['pagination'] = '';
        } else {
            $data['allDevice'] = $this->Devicelist_model->getNewsListByPagination($perPage, $offset);
        }


        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('admin/devicelist/all_device', $data);
        $this->load->view('admin/footer', $data);
    }

    public function history() {

        $data['title'] = 'Device History';
        $deviceStatus = NULL;
        $gcmStatus = NULL;
        $searchQuery = NULL;
        $userId = NULL;
        $userId = (!empty($this->input->get("user_id"))) ? $this->input->get("user_id") : NULL;
        $startDate = $this->session->userdata('start_date');
        $endDate = $this->session->userdata('end_date');

        //pagination
        $uri_segment = 4;
        $per_page = 10;
        $offset = 0;
        if ($this->uri->segment(4) === FALSE) {
            $offset = 0;
        } else {
            $offset = $this->uri->segment(4) ? $this->uri->segment(4) : 0;
        }

        if ($this->input->get()) {
            $deviceStatus = trim($this->input->get('device_status'));
            $gcmStatus = trim($this->input->get('gcm_status'));
            $getData = array();
            foreach ($this->input->get() as $key => $value) {
                $getData[$key] = $value;
            }

            $this->form_validation->set_data($getData);
            if ($this->input->get('search') == 1) {
                $this->form_validation->set_rules('search_query', '', 'trim');
            }
            if ($this->form_validation->run() == TRUE) {
                $searchQuery = trim($this->input->get('search_query'));
            } else {
                echo validation_errors();
            }
        }

        $allDeviceInfo = $this->Devicelist_model->getNewsListByPagination($userId, $per_page, $offset, $startDate, $endDate, $searchQuery, $deviceStatus, $gcmStatus);
        $data['allDevice'] = $allDeviceInfo['result'];

        $total_rows = $allDeviceInfo['totalRow'];
        generatePagging('device/monitor/history/', $total_rows, $per_page, $uri_segment, 4);

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('admin/devicelist/associated_devices', $data);
        $this->load->view('admin/footer', $data);
    }

    public function viewDeviceInfo($deviceId) {
        $data['tabActive'] = 'DeviceMonitor';
        $data['subTabActive'] = 'DeviceList';
        $data['deviceInfo'] = $this->Devicelist_model->getDeviceInfoById($deviceId);

        $this->load->view('admin/devicelist/all_device_view', $data);
    }

//    public function viewDeviceInfoByUUID($deviceUUID) {
//        $data['tabActive'] = 'DeviceMonitor';
//        $data['subTabActive'] = 'DeviceList';
//
//
//        $data['deviceInfo'] = $this->Devicelist_model->getDeviceInfoByUUID($deviceUUID);
//        $this->load->view('admin/devicelist/developer_device_view', $data);
//    }
//
//    public function addToDeveloperList($device_uuid) {
//        $data = array();
//        $data['error'] = '';
//        $data['title'] = 'Add Device List';
//        //$data['tabActive'] = 'addToDeveloperList';
//        $data['tabActive'] = 'DeviceMonitor';
//        $data['subTabActive'] = 'DeveloperDevices';
//        if ($this->input->post()) {
//            // $this->form_validation->set_rules('device_uuid', 'Device UUID', 'trim|required|is_unique[developer_devices.device_uuid]');
//            $this->form_validation->set_rules('device_uuid', 'Device UUID', 'trim|required');
//            $this->form_validation->set_rules('name', 'Name', 'trim|required');
//            if ($this->form_validation->run() == FALSE) {
//                $data['error'] = validation_errors();
//                $this->session->set_flashdata('info', $data['error']);
//
//                redirect('device/monitor/addToDeveloperList/' . $device_uuid);
//            } else {
//                $developerData = array();
//                $developerData['device_uuid'] = $this->input->post('device_uuid');
//                $developerData['name'] = $this->input->post('name');
//                if ($this->Devicelist_model->addToDeveloperList($developerData)) {
//                    $this->session->set_flashdata('success', 'Save Successful');
//                    redirect('device/monitor/manageDeveloperDevices');
//                } else {
//                    $data['error'] = "Failed to Save";
//                }
//            }
//        }
//
//        $data['device_uuid'] = $device_uuid;
////        $data['notificationList'] = $this->Devicelist_model->getNotificationsData();
//        $this->load->view('admin/header', $data);
//        $this->load->view('admin/navbar', $data);
//        $this->load->view('admin/sidebar', $data);
//        $this->load->view('admin/devicelist/add_to_developer_list', $data);
//        $this->load->view('admin/footer', $data);
//    }
//
//    public function manageDeveloperDevices() {
//
//        $data = array();
//        $data['error'] = '';
//        $data['title'] = 'Developer Devices';
//        $data['tabActive'] = 'manageDeveloperDevices';
//        $data['tabActive'] = 'DeviceMonitor';
//        $data['subTabActive'] = 'DeveloperDevices';
//        $data['developerDeviceList'] = $this->Devicelist_model->getDeveloperDevices();
//
////        $data['notificationList'] = $this->Devicelist_model->getNotificationsData();
//        $this->load->view('admin/header', $data);
//        $this->load->view('admin/navbar', $data);
//        $this->load->view('admin/sidebar', $data);
//        $this->load->view('admin/devicelist/manage_developer_devices', $data);
//        $this->load->view('admin/footer', $data);
//    }
//
//    public function editDeveloperInfo($id) {
//        $data = array();
//        $data['error'] = '';
//        $data['title'] = 'Developer Devices';
//        $data['tabActive'] = 'editDeveloperDevices';
//        $data['tabActive'] = 'DeviceMonitor';
//        $data['subTabActive'] = 'DeveloperDevices';
//        $data['id'] = $id;
//
//
//        if ($this->input->post()) {
//            $this->form_validation->set_rules('device_uuid', 'Device UUID', 'trim|required');
//            $this->form_validation->set_rules('name', 'Name', 'trim|required');
//            if ($this->form_validation->run() == FALSE) {
//                $data['error'] = validation_errors();
//                $this->session->set_flashdata('info', $data['error']);
//            } else {
//                $developerData['device_uuid'] = $this->input->post('device_uuid');
//                $developerData['name'] = $this->input->post('name');
//
//                if ($this->Devicelist_model->updateDeveloperInfo($developerData, $id)) {
//                    $this->session->set_flashdata('success', 'Update Successful');
//                    redirect('admin/device/monitor/manageDeveloperDevices');
//                } else {
//                    $data['error'] = "Failed to Save";
//                }
//            }
//        }
//
//        $data['developerDeviceInfo'] = $this->Devicelist_model->getDeveloperDevicesInfo($id);
////        $data['notificationList'] = $this->Devicelist_model->getNotificationsData();
//        $this->load->view('admin/header', $data);
//        $this->load->view('admin/navbar', $data);
//        $this->load->view('admin/sidebar', $data);
//        $this->load->view('admin/devicelist/edit_developer_devices', $data);
//        $this->load->view('admin/footer', $data);
//    }
//
//    public function deleteDeveloperDevice($developerId) {
//        $this->Devicelist_model->deleteDeveloperDeviceById($developerId);
//        $this->session->set_flashdata('success', 'Delete Successful');
//        redirect('admin/device/monitor/manageDeveloperDevices');
//    }
//
//    public function usesStatisticsByDevice($deviceUUDId) {
//        $data = array();
//        $data['error'] = '';
//        $data['title'] = 'Uses Statistics';
//        $data['tabActive'] = 'DeviceMonitor';
//        $data['subTabActive'] = 'DeviceList';
//        $where['startDate'] = $data['startDate'] = $this->input->get('startDate') ? $this->input->get('startDate') : date('Y-m-d', strtotime("-7 days"));
//        $where['endDate'] = $data['endDate'] = $this->input->get('endDate') ? $this->input->get('endDate') : date('Y-m-d');
//
//        // $data['tabActive'] = 'User Uses Statistics';
//        $data['userUsesStatistics'] = $this->Devicelist_model->getUserUsesStatistics($deviceUUDId, $where);
//        $data['userReadNewsDetails'] = $this->Devicelist_model->getUserNewsDetails($deviceUUDId, $where);
//        $data['userUserNewspaperRedirects'] = $this->Devicelist_model->getUserNewspaperRedirect($deviceUUDId, $where);
//
////        $data['notificationList'] = $this->Devicelist_model->getNotificationsData();
//        $this->load->view('admin/header', $data);
//        $this->load->view('admin/navbar', $data);
//        $this->load->view('admin/sidebar', $data);
//        $this->load->view('admin/devicelist/user_uses_statistics', $data);
//        $this->load->view('admin/footer', $data);
//    }
//
//    public function topDeviceByDate($offset = 0) {
//
//        $data = array();
//        $data['tabActive'] = 'DeviceMonitor';
//        $data['subTabActive'] = 'DeviceList';
//        $where['startDate'] = $data['startDate'] = $this->input->get('startDate') ? $this->input->get('startDate') : date('Y-m-d', strtotime("-30 days"));
//        $where['endDate'] = $data['endDate'] = $this->input->get('endDate') ? $this->input->get('endDate') : date('Y-m-d');
//
//        $perPage = 20;
//        $totalRow = $this->Devicelist_model->getTopPushDeviceTotal($data['startDate'], $data['endDate']);
//        $totalRow = $totalRow > 100 ? 100 : $totalRow;
//
//        $data['pagination'] = generatePagging(base_url('devicelist/topDeviceByDate'), $totalRow, $perPage);
//        $data['totalPushSent'] = $this->Devicelist_model->getTotalPushSent($data['startDate'], $data['endDate']);
//        $data['totalPushTapped'] = $this->Devicelist_model->getTotalPushTapped($data['startDate'], $data['endDate']);
//        $data['topPushDevice'] = $this->Devicelist_model->getTopPushDevice($data['startDate'], $data['endDate'], array('start' => $offset, 'item' => $perPage));
//
////        $data['notificationList'] = $this->Devicelist_model->getNotificationsData();
//
//        $this->load->view('admin/header', $data);
//        $this->load->view('admin/navbar', $data);
//        $this->load->view('admin/sidebar', $data);
//        $this->load->view('admin/devicelist/top_push_device_by_date', $data);
//        $this->load->view('admin/footer', $data);
//    }
//
//    public function getTappedNewsByDevice($uuid, $offset = 0) {
//        $data = array();
//        $data['error'] = '';
//        $data['tabActive'] = 'Push Tapped News by device';
//        $data['tabActive'] = 'DeviceMonitor';
//        $data['subTabActive'] = 'DeviceList';
//        $data['uuid'] = $uuid;
//
//        $perPage = 20;
//        $totalRow = $this->Global_model->countRow('push_tabbed_tracking', array('device_uuid' => $uuid));
//
//        $data['pagination'] = generatePagging(base_url('devicelist/getTappedNewsByDevice/' . $uuid), $totalRow, $perPage, 4);
//
//        $data['pushTappedNews'] = $this->Devicelist_model->getPushTappedNews($uuid, $offset, $perPage);
//        //  $data['pushTappedNews'] = $this->Devicelist_model->getPushTappedNews($uuid, $offset, $perPage);
////        $data['notificationList'] = $this->Devicelist_model->getNotificationsData();
//
//        $this->load->view('admin/header', $data);
//        $this->load->view('admin/navbar', $data);
//        $this->load->view('admin/sidebar', $data);
//        $this->load->view('admin/devicelist/push_tapped_news_by_device', $data);
//        $this->load->view('admin/footer', $data);
//    }

}
