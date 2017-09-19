<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2016-11-29
 *
 * bKash / SureCash merchant credentials.
 * Real values are provisioned per-merchant by the payment gateway and were
 * never meant to live in source control even at the time — kept here as
 * env-style placeholders for the redacted version of this codebase.
 */
$config['bmsisdn'] = getenv('BKASH_MSISDN') ?: 'YOUR_BKASH_MSISDN';
$config['buser'] = getenv('BKASH_USER') ?: 'YOUR_BKASH_USER';
$config['bpass'] = getenv('BKASH_PASS') ?: 'YOUR_BKASH_PASS';

$config['access_key'] = getenv('SURECASH_ACCESS_KEY') ?: 'YOUR_SURECASH_ACCESS_KEY';
$config['partner_code'] = getenv('SURECASH_PARTNER_CODE') ?: 'YOUR_SURECASH_PARTNER_CODE';
$config['client_id'] = getenv('SURECASH_CLIENT_ID') ?: 'YOUR_SURECASH_CLIENT_ID';
