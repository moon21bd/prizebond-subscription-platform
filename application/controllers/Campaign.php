<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-09-19
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Campaign extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function popUp() {
        header('Content-Type: application/json');
        
        $data['image_url'] = base_url() . 'assets/offer_banner/offer_banner_blue.jpg';
        if ($_POST['device_density'] == 'mdpi') {
            $imageFolder = 'mdpi';
        } elseif ($_POST['device_density'] == 'hdpi') {
            $imageFolder = 'hdpi';
        } elseif ($_POST['device_density'] == 'xhdpi') {
            $imageFolder = 'xhdpi';
        } elseif ($_POST['device_density'] == 'xxhdpi') {
            $imageFolder = 'xxhdpi';
        } elseif ($_POST['device_density'] == 'xxxhdpi') {
            $imageFolder = 'xxxhdpi';
        } else {
            $imageFolder = 'mdpi';
        }
        $data['campaign_banner_image_url'] = base_url() . 'assets/offer_banner/' . $imageFolder . '/offer_banner.jpg';
        $data['detail_url'] = 'https://prizebond-checker.com/offer?app=1';
        echo json_encode($data);
    }

    public function detail() {
        $data = array();
        $data['title'] = 'Campaign Details';
        $this->load->view('campaign_content', $data);
    }

}
