<?php

//require('../../../config.php');
require_once(__DIR__ . '/config.php');
global $CFG;

if (!defined('PROJECT_PATH')) {
    define('PROJECT_PATH', $CFG->wwwroot.'/enrol/sslcommerz'); // Replace this value with your project path
}

if (!defined('API_DOMAIN_URL')) {
    define('API_DOMAIN_URL', get_config('enrol_sslcommerz')->apiurl);
}

if (!defined('STORE_ID')) {
    define('STORE_ID', get_config('enrol_sslcommerz')->sslstoreid);
}

if (!defined('STORE_PASSWORD')) {
    define('STORE_PASSWORD', get_config('enrol_sslcommerz')->sslstorepassword);
}

if (!defined('IS_LOCALHOST')) {
    define('IS_LOCALHOST', get_config('enrol_sslcommerz')->productionenv);
}

return [
    'projectPath' => constant("PROJECT_PATH"),
    'apiDomain' => constant("API_DOMAIN_URL"),
    'apiCredentials' => [
        'store_id' => constant("STORE_ID"),
        'store_password' => constant("STORE_PASSWORD"),
    ],
    'apiUrl' => [
        'make_payment' => "/gwprocess/v4/api.php",
        'transaction_status' => "/validator/api/merchantTransIDvalidationAPI.php",
        'order_validate' => "/validator/api/validationserverAPI.php",
        'refund_payment' => "/validator/api/merchantTransIDvalidationAPI.php",
        'refund_status' => "/validator/api/merchantTransIDvalidationAPI.php",
    ],
    'connect_from_localhost' => constant("IS_LOCALHOST"),
    'success_url' => 'success.php',
    'failed_url' => 'fail.php',
    'cancel_url' => 'cancel.php',
    'ipn_url' => 'ipn.php',
];
