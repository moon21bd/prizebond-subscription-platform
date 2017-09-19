<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-09-14
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {

    const USER_ROLE_CRM = 'CRM Officer';

    public function __construct() {
        parent::__construct();

        $this->load->model('global_model');
        $this->load->library('encryption');
        $this->load->model('Main_model');
        $this->projectId = 6;

        if ($this->session->userdata('logged_in')) {
            if ($this->session->userdata('redirectURL')) {
                $redirect = $this->session->userdata('redirectURL');
                $this->session->unset_userdata('redirectURL');
                redirect($redirect);
            }
        }

        if (empty($this->session->userdata('start_date')) && (empty($this->session->userdata('end_date')))) {
            $this->session->set_userdata('start_date', date('Y-m-d', strtotime('-30 Days')));
            $this->session->set_userdata('end_date', date('Y-m-d'));
        }
    }

    public function verifyUser() {

        $this->load->library('session');

        if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '192.168.50.53' || $_SERVER['HTTP_HOST'] == '192.168.50.54') {
            $sessionData = array(
                'userId' => 1,
                'userRole' => 'Local Role',
                'userName' => 'Local User',
                'logged_in' => TRUE
            );
            $this->session->set_userdata($sessionData);
            return 1;
        }

        if ($this->session->userdata('logged_in') && $this->session->userdata('permissions')) {
            if ($this->checkPermissions() == 1) {
                return 1;
            }
            return 0;
        } else {

            $userCredential = $this->getUserCredential();

            if (!empty($userCredential)) {

                if ($userCredential['projectUrl'] == $_SERVER['HTTP_HOST']) {
                    if (!empty($userCredential['userRole']) && (!empty($userCredential['userName'])) && (!empty($userCredential['permission']))) {
                        $sessionData = array(
                            'userId' => $userCredential['userId'],
                            'userRole' => $userCredential['userRole'],
                            'userName' => $userCredential['userName'],
                            'permissions' => $userCredential['permission'],
                            'logged_in' => TRUE
                        );
                        $this->session->set_userdata($sessionData);
                        if (strpos($userCredential['permission'], "*") !== FALSE) {
                            redirect('/admin');
                            exit();
                        } else {
                            $arr = explode(",", $userCredential['permission']);
                            redirect('/' . $arr[0]);
                            exit();
                        }
                    }
                    return 0;
                }
                return 0;
            }
        }
        return 0;
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('http://manage.example.com/login');
    }

    public function getInfo() {

        echo $_SERVER['SERVER_ADDR'];
        echo "<br>";
        echo $this->input->ip_address();

        $data['class'] = get_class($this);
        $class = new ReflectionClass($this);
        $methods = $class->getMethods(ReflectionMethod::IS_FINAL);
        if (count($methods) > 0) {
            foreach ($methods as $method) {
                $data['final_methods'][] = $method->name;
            }
        }
        echo json_encode($data);
        exit();
    }

    private function checkPermissions() {
        //prizebond-checker.com-->>Users,Orders,Message
        $className = get_class($this);
        if (strpos($this->session->userdata('permissions'), '*') !== false) {
            return 1;
        } else if (strpos($this->session->userdata('permissions'), '-->>') !== false) {
            $arr = explode('-->>', $this->session->userdata('permissions'));
            $permissionArray = explode(',', $arr[1]);

            $controllerArray = array();
            foreach ($permissionArray as $controllerName) {
                $controllerArray[] = ucfirst(substr($controllerName, strrpos($controllerName, '/') + 1, strlen($controllerName)));
            }

            if (in_array($className, $controllerArray)) {
                return 1;
            }
        } else if ($this->session->userdata('permissions') == $className) {
            return 1;
        }
        return 0;
    }

    private function getUserCredential() {
        $sessionData = $this->input->get('session_data');
        $sessionData = $this->encryption->decrypt(base64_decode($sessionData));

        $data = array();
        if (strpos($sessionData, ':') !== false) {
            $array = explode(':', $sessionData);

            $userInfo = explode('-', $array[0]);

            $userId = $userInfo[0];
            $userName = $userInfo[1];
            $userRole = $userInfo[2];

            if (strpos($array[1], '|') !== false) {
                $permissionArray = explode('|', $array[1]);
                if (in_array('manage', $permissionArray)) {
                    $permission = 'manage';
                }
            } else {
                $permission = $array[1];
            }
            $projectUrl = $array[2];

            $data['userId'] = $userId;
            $data['userName'] = $userName;
            $data['userRole'] = $userRole;
            $data['projectUrl'] = $projectUrl;
            $data['permission'] = $permission;
        }
        return $data;
    }

    protected function login() {
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, strpos($_SERVER["SERVER_PROTOCOL"], '/'))) . '://';
        $this->session->set_userdata('redirectURL', current_url());
        redirect($protocol . '/manage.example.com/account?redirect_url= ' . current_url());
    }

    protected function checkAuth() {
        if ($this->verifyUser() == 0) {
            $this->login();
            exit;
        }
        return TRUE;
    }

    protected function checkHost() {
        if ($_SERVER['HTTP_HOST'] != 'localhost') {
            return TRUE;
        }
    }

    protected function sendMailUsingSMTP($recipientEmailAddress, $subject, $message) {
        $config = array('protocol' => 'smtp',
            'smtp_host' => 'mail.prizebond-checker.com',
            'smtp_port' => '25',
            'smtp_user' => 'support@prizebond-checker.com',
            'smtp_pass' => getenv('SMTP_PASS') ?: 'YOUR_SMTP_PASSWORD',
            'charset' => 'utf-8',
            'newline' => '\r\n',
            'mailtype' => 'html',
            'smtp_timeout' => 45,
            'validation' => TRUE
        );

        $this->load->library('email');
        $this->email->initialize($config);
        $this->email->from('support@prizebond-checker.com', 'Support');
        $this->email->to($recipientEmailAddress);
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->set_mailtype("html");

        if ($this->email->send()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

}
