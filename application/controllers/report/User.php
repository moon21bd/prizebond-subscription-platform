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

class User extends Main {

    function __construct() {
        parent::__construct();
        $this->load->model('report/Users_model');
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }

        if ($this->checkHost()) {
            $this->checkAuth();
        }
    }

    public function index() {

        if ($this->input->post()) {
            $date = $this->input->post('date');
            $data['date'] = $date;
        } else {
            $date = date('Y-m-d');
            $data['date'] = $date;
        }
        $data['active_tab'] = 'user reports';

        $android = 2;
        $iOS = 1;

        $data['title'] = 'User Reports';

        $array = array();
        $dateArray = array();
        for ($i = 5; $i >= 0; $i--) {
            $dateArray[] = date('Y-m-d', strtotime($date . "-$i day"));
        }

        rsort($dateArray);

        $total = count($dateArray) - 1;
        $counter = 0;

        foreach ($dateArray as $date) {

            $array['Total Added User'][$date]['android'] = $this->Users_model->getAddedTotalUserByDate($date, $android);
            $array['Total Added User'][$date]['ios'] = $this->Users_model->getAddedTotalUserByDate($date, $iOS);

            $array['Total Active (TTAU) User'][$date]['android'] = $this->Users_model->getTotalActiveUserByDate($date, $android);
            $array['Total Active (TTAU) User'][$date]['ios'] = $this->Users_model->getTotalActiveUserByDate($date, $iOS);
            if ($counter == 0) {

                $key_1 = '';
                $startDate = date('Y-m-d', strtotime($date));
                $endDate = date('Y-m-d', strtotime($date . '-6 day'));
                $key_1 = 'TAU who get Added in last 7 days (' . $startDate . ' - ' . $endDate . ')';

                $key_2 = '';
                $startDate = date('Y-m-d', strtotime($date . '-7 day'));
                $endDate = date('Y-m-d', strtotime($date . '-29 day'));
                $key_2 = 'TAU who get Added in last 8-30 days (' . $startDate . ' - ' . $endDate . ')';

                $key_3 = '';
                $startDate = date('Y-m-d', strtotime($date . '-30 day'));
                $endDate = date('Y-m-d', strtotime($date . '-44 day'));
                $key_3 = 'TAU who get Added in last 30-45 days (' . $startDate . ' - ' . $endDate . ')';

                $key_4 = '';
                $startDate = date('Y-m-d', strtotime($date . '-45 day'));
                $endDate = date('Y-m-d', strtotime($date . '-59 day'));
                $key_4 = 'TAU who get Added in last 45-60 days (' . $startDate . ' - ' . $endDate . ')';

                $key_5 = '';
                $startDate = date('Y-m-d', strtotime($date . '-60 day'));
                $endDate = date('Y-m-d', strtotime($date . '-89 day'));
                $key_5 = 'TAU who get Added in last 60-90 days (' . $startDate . ' - ' . $endDate . ')';

                $key_6 = '';
                $startDate = date('Y-m-d', strtotime($date . '-90 day'));
                $endDate = date('Y-m-d', strtotime($date . '-179 day'));
                $key_6 = 'TAU who get Added in last 90-180 days (' . $startDate . ' - ' . $endDate . ')';

                $key_7 = '';
                $startDate = date('Y-m-d', strtotime($date . '-180 day'));
                $endDate = date('Y-m-d', strtotime($date . '-364 day'));
                $key_7 = 'TAU who get Added in last 180-364 days (' . $startDate . ' - ' . $endDate . ')';

                $key_8 = '';
                $startDate = date('Y-m-d', strtotime($date . '-365 day'));
                $key_8 = 'TAU who get Added before 365+ days (' . $startDate . ')';
            } else if ($counter == $total) {
                $counter = 0;
            }

            $startDate = date('Y-m-d', strtotime($date));
            $endDate = date('Y-m-d', strtotime($date . '-6 day'));

            $array[$key_1][$date]['ios'] = $this->Users_model->countUsersByDateRange($startDate, $endDate, $date, $iOS);
            $array[$key_1][$date]['android'] = $this->Users_model->countUsersByDateRange($startDate, $endDate, $date, $android);

            $startDate = date('Y-m-d', strtotime($date . '-7 day'));
            $endDate = date('Y-m-d', strtotime($date . '-29 day'));

            $array[$key_2][$date]['ios'] = $this->Users_model->countUsersByDateRange($startDate, $endDate, $date, $iOS);
            $array[$key_2][$date]['android'] = $this->Users_model->countUsersByDateRange($startDate, $endDate, $date, $android);

            $startDate = date('Y-m-d', strtotime($date . '-30 day'));
            $endDate = date('Y-m-d', strtotime($date . '-44 day'));

            $array[$key_3][$date]['ios'] = $this->Users_model->countUsersByDateRange($startDate, $endDate, $date, $iOS);
            $array[$key_3][$date]['android'] = $this->Users_model->countUsersByDateRange($startDate, $endDate, $date, $android);

            $startDate = date('Y-m-d', strtotime($date . '-45 day'));
            $endDate = date('Y-m-d', strtotime($date . '-59 day'));

            $array[$key_4][$date]['ios'] = $this->Users_model->countUsersByDateRange($startDate, $endDate, $date, $iOS);
            $array[$key_4][$date]['android'] = $this->Users_model->countUsersByDateRange($startDate, $endDate, $date, $android);

            $startDate = date('Y-m-d', strtotime($date . '-60 day'));
            $endDate = date('Y-m-d', strtotime($date . '-89 day'));

            $array[$key_5][$date]['ios'] = $this->Users_model->countUsersByDateRange($startDate, $endDate, $date, $iOS);
            $array[$key_5][$date]['android'] = $this->Users_model->countUsersByDateRange($startDate, $endDate, $date, $android);

            $startDate = date('Y-m-d', strtotime($date . '-90 day'));
            $endDate = date('Y-m-d', strtotime($date . '-179 day'));

            $array[$key_6][$date]['ios'] = $this->Users_model->countUsersByDateRange($startDate, $endDate, $date, $iOS);
            $array[$key_6][$date]['android'] = $this->Users_model->countUsersByDateRange($startDate, $endDate, $date, $android);

            $startDate = date('Y-m-d', strtotime($date . '-180 day'));
            $endDate = date('Y-m-d', strtotime($date . '-364 day'));

            $array[$key_7][$date]['ios'] = $this->Users_model->countUsersByDateRange($startDate, $endDate, $date, $iOS);
            $array[$key_7][$date]['android'] = $this->Users_model->countUsersByDateRange($startDate, $endDate, $date, $android);

            $startDate = date('Y-m-d', strtotime($date . '-365 day'));

            $array[$key_8][$date]['ios'] = $this->Users_model->countUsersBeforeOneYear($date, $startDate, $iOS);
            $array[$key_8][$date]['android'] = $this->Users_model->countUsersBeforeOneYear($date, $startDate, $android);

            $counter++;
        }

        //$data['notificationList'] = $this->Users_model->getNotificationsData();
        $data['output'] = $array;
        //$data['layout'] = $this->load->view('users', $data, TRUE);
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('admin/report/users', $data);
        $this->load->view('admin/footer', $data);
    }

}
