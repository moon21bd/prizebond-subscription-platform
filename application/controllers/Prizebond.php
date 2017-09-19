<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-05-03
 */

defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Example
 *
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Phil Sturgeon
 * @link		http://philsturgeon.co.uk/code/
 */
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class Prizebond extends REST_Controller {

    function __construct() {
        // Construct our parent class
        parent::__construct();
        
        $this->load->model('api_model');
        $this->load->model('global_model');
        $this->load->library('form_validation');
    }

    public function verifyAPIKey($apiKey, $secretKey = FALSE) {
        if (!$this->api_model->doesMatchApiKey($apiKey)) {
            $output['response']['error'] = 'api key not matched';
            $this->response($output);
            exit;
        } else {
            return TRUE;
        }
    }

    /*
    public function updateAPIKey_post() {
        $updateData = array(
            'update_api_key' => $this->input->post('update_api_key')
        );
        return $this->api_model->updateAPIKey($updateData);
    }*/

    public function savePrizeBondNumbers($userId, $uuid, $prizebondNumbers, $bond_series) {
        return $this->api_model->savePrizeBondNumbers($userId, $uuid, $prizebondNumbers, $bond_series);
    }

    public function deletePrizeBondNumbers($data, $userId) {
        return $this->api_model->deletePrizeBondNumbers($data, $userId);
    }

    public function getSeriesList() {
        return $this->api_model->getSeriesList();
    }

    public function getListOfDraw() {
        return $this->api_model->getListOfDraw();
    }

    public function getResultOfADraw($draw_id) {
        return $this->api_model->getResultOfADraw($draw_id);
    }

    public function updateDeviceInfo($requestedData) {
        return $this->api_model->updateDeviceInfo($requestedData);
    }

    public function getLatestPrizebondDraw() {
        return $this->api_model->getLatestPrizebondDraw();
    }

    public function getDeviceInfoForSendPush() {
        return $this->api_model->getDeviceInfoForSendPush();
    }

    public function savePushStatus($id) {
        return $this->api_model->savePushStatus($id);
    }

    public function saveDrawInfoTablePushStatus($id) {
        return $this->api_model->saveDrawInfoTablePushStatus($id);
    }

    public function getPushId($deviceUuid) {
        return $this->api_model->getPushId($deviceUuid);
    }

}
