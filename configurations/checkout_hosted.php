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
 * sslcommerz enrolments plugin settings and presets.
 *
 * @package    enrol_sslcommerz
 * @copyright  2021 Brain station 23 ltd.
 * @author     Brain station 23 ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

# This is a sample page to understand how to connect payment gateway

require_once(__DIR__ . "/lib/SslCommerzNotification.php");

include("OrderTransaction.php");

use SslCommerz\SslCommerzNotification;

global $DB,$USER;

$userid             = required_param('userid', PARAM_INT);
$customer_name      = required_param('customer_name', PARAM_TEXT);
$customer_email     = required_param('customer_email', PARAM_TEXT);
$customer_address   = required_param('customer_address', PARAM_TEXT);
$customer_city      = optional_param('customer_city', '',PARAM_TEXT);
$customer_state     = optional_param('customer_state', '', PARAM_TEXT);
$customer_zip       = optional_param('customer_zip', '', PARAM_TEXT);
$customer_mobile    = required_param('customer_mobile', PARAM_TEXT);
$customer_country   = required_param('customer_country', PARAM_TEXT);
$categoryname       = required_param('categoryname', PARAM_TEXT);
$categoryid         = required_param('categoryid', PARAM_INT);
$amount             = required_param('amount', PARAM_FLOAT);
$courseid           = required_param('course_id', PARAM_INT);
$instance_id        = required_param('instance_id', PARAM_INT);

# Organize the submitted/inputted data
$post_data = array();

$post_data['total_amount'] = $amount;
$post_data['currency'] = "BDT";
$post_data['tran_id'] = "SSLCZ_TEST_" . uniqid();

# CUSTOMER INFORMATION
$post_data['cus_name'] = isset($customer_name) ? $customer_name : "John Doe";
$post_data['cus_email'] = isset($customer_email) ? $customer_email : "john.doe@email.com";
$post_data['cus_add1'] = isset($customer_address) ? $customer_address : "Dhaka";
$post_data['userid'] = isset($userid) ? $userid : $USER->id;
$post_data['cus_add2'] = "Dhaka";
$post_data['cus_city'] = isset($customer_city) ? $customer_address :"Dhaka";
$post_data['cus_state'] = isset($customer_state) ? $customer_state :"Dhaka";
$post_data['cus_postcode'] = isset($customer_zip) ? $customer_zip :"Dhaka";
$post_data['cus_country'] = "Bangladesh";
$post_data['cus_phone'] = isset($customer_mobile) ? $customer_mobile : "";
$post_data['cus_fax'] = "01711111111";

# SHIPMENT INFORMATION
$post_data['ship_name'] = "Store Test";
$post_data['ship_add1'] = "Dhaka";
$post_data['ship_add2'] = "Dhaka";
$post_data['ship_city'] = "Dhaka";
$post_data['ship_state'] = "Dhaka";
$post_data['ship_postcode'] = "1000";
$post_data['ship_phone'] = "";
$post_data['ship_country'] = "Bangladesh";

# OPTIONAL PARAMETERS
$post_data['value_a'] = $USER->id;
$post_data['value_b'] = isset($categoryid) ? $categoryid : "";

$post_data['value_c'] = $courseid;
$post_data['value_d'] = $instance_id;


# CART PARAMETERS
$post_data['cart'] = json_encode(array(
    array("sku" => "REF0001", "product" => "DHK TO BRS AC A1", "quantity" => "1", "amount" => "200.00"),
    array("sku" => "REF0002", "product" => "DHK TO BRS AC A2", "quantity" => "1", "amount" => "200.00"),
    array("sku" => "REF0003", "product" => "DHK TO BRS AC A3", "quantity" => "1", "amount" => "200.00"),
    array("sku" => "REF0004", "product" => "DHK TO BRS AC A4", "quantity" => "2", "amount" => "200.00")
));

# RECURRING DATA
$schedule = array(
    "refer" => "5B90BA91AA3F2", # Subscriber id which generated in Merchant Admin panel
    "acct_no" => "01730671731",
    "type" => "daily", # Recurring Schedule - monthly,weekly,daily

);

# MORE THAN 20 Characaters - Alpha-Numeric - For Auto debit Instruction
# IT Will Return Transaction History
# IT Will Return Saved Card- Set Default and delete Option

$post_data["firstName"] = isset($customer_name) ? $customer_name : "John Doe";
$post_data["lastName"] = "";
$post_data["street"] = isset($customer_address) ? $customer_address : "";
$post_data["city"] = isset($customer_state) ? $customer_state : "";
$post_data["state"] = isset($customer_state) ? $customer_state : "";
$post_data["postalCode"] = isset($customer_zip) ? $customer_zip : "";
$post_data["country"] = isset($customer_country) ? $_POST['customer_country'] : "";
$post_data["email"] = isset($customer_email) ? $customer_email : "";

$post_data["product_category"] = isset($categoryid) ? $categoryid : "";
$post_data["product_name"] = isset($categoryname) ? $categoryname : "";
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

$post_data["product_type"] = "Prepaid";
$post_data["phone_number"] = isset($customer_mobile) ? $customer_mobile : "";
$post_data["country_topUp"] = "Bangladesh";

# SPECIAL PARAM
$post_data['tokenize_id'] = "1";

# 1 : Physical Goods
# 2 : Non-Physical Goods Vertical(software)
# 3 : Airline Vertical Profile
# 4 : Travel Vertical Profile
# 5 : Telecom Vertical Profile

$post_data["product_profile"] = "general";
$post_data["product_profile_id"] = "5";

$post_data["topup_number"] = isset($customer_mobile) ? $customer_mobile : ""; # topUpNumber

# First, save the input data into local database table `orders`
$query = new OrderTransaction();
$sqllog = $query->logTransactionQuery($post_data);
$sql = $query->saveTransactionQuery($post_data);
$DB->execute($sqllog);

if ($DB->execute($sql)) {
    # Call the Payment Gateway Library
    $sslcomz = new SslCommerzNotification();
    $sslcomz->makePayment($post_data, 'hosted');


} else {
    echo "Error: " . $sql . "<br> Database connection Error";
}

