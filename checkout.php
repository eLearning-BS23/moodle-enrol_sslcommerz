<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * sslcommerz enrolment plugin - support for user self unenrolment.
 *
 * @package    enrol_sslcommerz
 * @copyright  2021 Brain station 23 ltd.
 * @author     Brain station 23 ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require("../../config.php");
global $CFG, $USER, $DB;
require_login();

$total_amount   = required_param('amount', PARAM_FLOAT);
$currency       = required_param('currency_code', PARAM_TEXT);
$course_id      = required_param('course_id', PARAM_ALPHAEXT);
$user_id        = required_param('user_id', PARAM_INT);
$instance_id    = required_param('instance_id',PARAM_INT);
$cus_name       = required_param('os0', PARAM_TEXT);
$cus_email      = required_param('email', PARAM_TEXT);

// OPTIONAL PARAMETERS.
$cus_add1       = optional_param('address',0, PARAM_TEXT);
$cus_city       = optional_param('city',0, PARAM_TEXT);
$cus_country    = optional_param('cus_country', 0, PARAM_TEXT);

$value_a = optional_param('custom', 0, PARAM_ALPHAEXT);
$value_b = optional_param('course_id', 0, PARAM_INT);
$value_c = optional_param('user_id', 0, PARAM_INT);
$value_d = optional_param('instance_id', 0, PARAM_INT);

//static data
$post_data["previous_customer"] = "Yes";
$post_data["shipping_method"] = "online";
$post_data["num_of_item"] = "1";
$post_data["product_shipping_contry"] = "Bangladesh";
$post_data["vip_customer"] = "YES";
$post_data["hours_till_departure"] = "12 hrs";
$post_data["flight_type"] = "Oneway";
$post_data["journey_from_to"] = "DAC-CGP";
$post_data["third_party_booking"] = "No";

$post_data["hotel_name"] = "Sheraton";
$post_data["length_of_stay"] = "2 days";
$post_data["check_in_time"] = "24 hrs";
$post_data["hotel_city"] = "Dhaka";


/* PHP */

$postdata = array();
$postdata['store_id'] = get_config('enrol_sslcommerz')->sslstoreid;
$postdata['store_passwd'] = get_config('enrol_sslcommerz')->sslstorepassword;
$postdata['productionenv'] = get_config('enrol_sslcommerz')->productionenv;
$postdata['total_amount'] = $total_amount;

$productionenv = $postdata['productionenv'];
if($productionenv == "") {
    $productionenv = false;
}

$postdata['tran_id'] = "MD_COURSE_" . uniqid();

$postdata['success_url'] = $CFG->wwwroot . "/enrol/sslcommerz/success.php?id=" . $course_id;
$postdata['fail_url'] = $CFG->wwwroot . "/enrol/sslcommerz/fail.php?id=" . $course_id;
$postdata['cancel_url'] = $CFG->wwwroot . "/enrol/sslcommerz/cancel.php?id=" . $course_id;
$postdata['ipn_url'] = $CFG->wwwroot . "/enrol/sslcommerz/ipn.php?id=" . $course_id;


$postdata['cus_add2'] = "";
$postdata['cus_state'] = "";
$postdata['cus_postcode'] = "1000";
$postdata['cus_phone'] = "";
$postdata['cus_fax'] = "";

$data = new stdClass();

$data->userid = $user_id;
$data->courseid = $course_id;
$data->instanceid = $instance_id;
$data->payment_currency = $currency;
$data->payment_status = 'Pending';
$data->txn_id = $postdata['tran_id'];
$data->timeupdated = time();

$DB->insert_record("enrol_sslcommerz", $data);

// REQUEST SEND TO SSLCOMMERZ.
$directapiurl = get_config("enrol_sslcommerz")->apiurl;

$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $directapiurl);   //The URL to fetch.
curl_setopt($handle, CURLOPT_TIMEOUT, 30);     //The maximum number of seconds to allow cURL functions to execute.
curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);  //The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
curl_setopt($handle, CURLOPT_POST, 1);  //This POST is the normal application/x-www-form-urlencoded kind, most commonly used by HTML forms.
curl_setopt($handle, CURLOPT_POSTFIELDS, $postdata);  //The full data to post in a HTTP "POST" operation.
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, $productionenv); // KEEP IT FALSE IF YOU RUN FROM LOCAL PC.

$content = curl_exec($handle);
$code    = curl_getinfo($handle, CURLINFO_HTTP_CODE);
if ($code == 200 && !(curl_errno($handle))) {
    curl_close($handle);
    $sslcommerzresponse = $content;
} else {
    curl_close($handle);
    echo "FAILED TO CONNECT WITH SSLCOMMERZ API";
    exit;
}
// PARSE THE JSON RESPONSE.
$sslcz = json_decode($sslcommerzresponse, true);

if (isset($sslcz['GatewayPageURL']) && $sslcz['GatewayPageURL'] != "") {
    // THERE ARE MANY WAYS TO REDIRECT - Javascript, Meta Tag or Php Header Redirect or Other
    // echo "<script>window.location.href = '". $sslcz['GatewayPageURL'] ."';</script>";
    echo "<meta http-equiv='refresh' content='0;url=" . $sslcz['GatewayPageURL'] . "'>";
    // ... header("Location: ". $sslcz['GatewayPageURL']);
    exit;
} else {
    echo "JSON Data parsing error!";
}



# First, save the input data into local database table `orders`
//$query = new OrderTransaction();
//$sql = $query->saveTransactionQuery($post_data);
//
//if ($DB->execute($sql)) {
//    # Call the Payment Gateway Library
//    $sslcomz = new SslCommerzNotification();
//    $sslcomz->makePayment($post_data, 'hosted');
//} else {
//    echo "Error: " . $sql . "<br> Database connection Error";
//}
