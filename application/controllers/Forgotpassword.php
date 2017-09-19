<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-11
 */

class Forgotpassword extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('bitbirds');
        $this->load->model('forgotpassword_model');
        $this->load->helper('string');
    }

    public function forgotPasswordForm() {
        $step = '';
        $step = $this->input->get('step');
        $data['step'] = $step;

        if (!empty($step) && $step == 1) {

            $data = array();

            if ($this->input->post()) {

                $input = $this->input->post('email_or_mobile');

                if (isset($input) && !empty($input)) {

                    $verifyCode = random_string('numeric', 4);
                    $senderText = 'PRIZEBOND';
                    $smsText = 'Your prize bond password recovery code is ' . $verifyCode;

                    if (strpos($input, '@') !== false) {
                        if (!filter_var($input, FILTER_VALIDATE_EMAIL) === false) {
                            $userInfo = $this->forgotpassword_model->getUserInfo('user', array('email' => $input, 'verify_status' => "YES", 'status' => 1));
                        } else {
                            $data['error'] = 'Invalid email address';
                            $this->session->set_flashdata('error', 'Invalid email address');
                            redirect('forgotpassword/forgotPasswordForm?step=1');
                        }
                    } else {
                        if (strlen($input) == 14) {
                            $userInfo = $this->forgotpassword_model->getUserInfo('user', array('mobile_number' => $input, 'verify_status' => "YES", 'status' => 1));
                        } else {
                            $data['error'] = 'Invalid mobile number. Please try with country code.';
                            $this->session->set_flashdata('error', 'Invalid mobile number. Please try with country code.');
                            redirect('forgotpassword/forgotPasswordForm?step=1');
                        }
                    }

                    if (isset($userInfo) && is_array($userInfo)) {
                        if (!$this->forgotpassword_model->checkVerifyCodeSendCounter('forgot_verify_counter', array('user_id' => $userInfo['id']))) {
                            $data['error'] = 'ইতিমধ্যে আপনাকে ৩ বার ভেরিফিকাশন কোড পাঠানো হয়েছে। আবার ২৪ ঘণ্টা পরে চেষ্টা করতে পারবেন।';
                            $this->session->set_flashdata('error', $data['error']);
                            redirect('forgotpassword/forgotPasswordForm?step=1');
                        } else {

                            /*
                              $this->load->library('email');
                              $from = 'no-reply@prizebond-checker.com';
                              $subject = 'Recovery Code';
                              $this->email->from($from, 'PRIZEBOND');
                              $this->email->to($userInfo['email']);
                              $this->email->subject($subject);
                              $mailer = "Dear " . $userInfo['name'] . ",<br/>";
                              $mailer .= "<strong>Your prizebond-checker.com Account Verification Code is:</strong> " . $verifyCode . "<br/></br> Prizebond Checker Team";
                              $this->email->message($mailer);
                              $this->email->set_mailtype("html");
                              if ($this->email->send()) {
                              $sessionInfo = array(
                              'email' => $userInfo['email'],
                              'verify_code' => $verifyCode,
                              );
                              $this->session->set_userdata($sessionInfo);
                              }
                             * 
                             */

                            $params = array(
                                'recipient_mobile_number' => $userInfo['mobile_number'],
                                'message' => $smsText,
                                'sender_mask' => NULL
                            );

                            $this->load->library('smsrouter', $params);
                            $response = $this->smsrouter->send();

                            //if ($this->bitbirds->sendSMS($senderText, $smsText, $userInfo['mobile_number'])) {
                            if (!empty($response) && is_array($response)) {
                                if ($response['type'] == 'success') {

                                    if (!$this->forgotpassword_model->doesExit('forgot_verify_counter', array('user_id' => $userInfo['id']))) {

                                        $verifyCounterData = array(
                                            'user_id' => $userInfo['id'],
                                            'counter' => 1,
                                            'create_date_time' => date('Y-m-d H:i:s'),
                                            'update_date_time' => date('Y-m-d H:i:s')
                                        );
                                        $this->forgotpassword_model->insert('forgot_verify_counter', $verifyCounterData);
                                    } else {
                                        $verifyCounterData = array(
                                            'user_id' => $userInfo['id']
                                        );
                                        $this->forgotpassword_model->updateForgotCounter('forgot_verify_counter', $verifyCounterData);
                                    }
                                    $sessionInfo = array(
                                        'mobile' => $userInfo['mobile_number'],
                                        'verify_code' => $verifyCode,
                                    );

                                    $this->session->set_userdata($sessionInfo);
                                    redirect('forgotpassword/forgotPasswordForm?step=2');
                                } else {
                                    $data['error'] = 'ভেরিফিকশন কোড পাঠানো যায় নাই।';
                                }
                            } else {
                                $data['error'] = 'ভেরিফিকশন কোড পাঠানো যায় নাই।';
                            }
                        }
                    } else {
                        $data['error'] = 'Invalid email or phone number';
                    }
                } else {
                    $data['error'] = 'Please enter email or phone number';
                }
            }

            $this->load->view('forgot_password/step_one', $data);
        }

        if (!empty($step) && $step == 2) {

            $data = array();

            if ($this->session->userdata('mobile') or $this->session->userdata('email')) {

                if ($this->session->userdata('mobile')) {
                    $userInfo = $this->forgotpassword_model->getUserInfo('user', array('mobile_number' => $this->session->userdata('mobile')));
                    $this->session->set_userdata('user_id', $userInfo['id']);
                } else {
                    $userInfo = $this->forgotpassword_model->getUserInfo('user', array('email' => $this->session->userdata('email')));
                    $this->session->set_userdata('user_id', $userInfo['id']);
                }

                if ($this->input->post()) {
                    $verifyCode = $this->input->post('verify_code');
                    if (isset($verifyCode) && !empty($verifyCode)) {
                        if ($verifyCode == $this->session->userdata('verify_code')) {
                            $this->session->set_userdata('verified', TRUE);
                            redirect('forgotpassword/forgotPasswordForm?step=3');
                        } else {
                            $data['error'] = 'ভেরিফিকেশন কোডটি ভুল দিয়েছেন';
                            $this->session->set_flashdata('error', 'ভেরিফিকেশন কোডটি ভুল দিয়েছেন');
                            redirect('forgotpassword/forgotPasswordForm?step=2');
                        }
                    } else {
                        $data['error'] = 'ভেরিফিকেশন কোডটি ভুল দিয়েছেন';
                    }
                }
                $this->load->view('forgot_password/step_two', $data);
            } else {
                redirect('forgotpassword/forgotPasswordForm?step=1');
            }
        }


        if (!empty($step) && $step == 3) {

            if ($this->session->userdata('user_id') AND $this->session->userdata('verified')) {

                if ($this->input->post()) {
                    $password = '';
                    $retype_password = '';

                    $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]|max_length[20]');
                    $this->form_validation->set_rules('retype_password', 'Password confirmation', 'required|matches[password]');

                    if ($this->form_validation->run()) {
                        $password = $this->input->post('password');

                        $password = md5($password);
                        $this->forgotpassword_model->update('user', array('password' => $password), array('id' => $this->session->userdata('user_id')));
                        $this->session->set_userdata('complited', TRUE);
                        redirect('forgotpassword/forgotPasswordForm?step=4');
                    } else {
                        $data['error'] = validation_errors();
                    }
                }
                $this->load->view('forgot_password/step_three', $data);
            } else {
                redirect('forgotpassword/forgotPasswordForm?step=2');
            }
        }

        if (!empty($step) && $step == 4) {
            if ($this->session->userdata('complited')) {
                $this->session->sess_destroy();
                $this->load->view('forgot_password/step_four');
            } else {
                redirect('forgotpassword/forgotPasswordForm?step=3');
            }
        }
    }

}
