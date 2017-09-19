<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-05-17
 */

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require 'Main.php';

class Auth extends Main {

    public function __construct() {
        parent::__construct();
    }

    public final function index() {
        if ($this->checkAuth() == TRUE) {
            redirect('admin');
        } else {
            $this->login();
            exit;
        }
    }

}
