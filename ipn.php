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

defined('MOODLE_INTERNAL') || die();

// @codingStandardsIgnoreLine This script does not require login.
require("../../config.php");
require_once("lib.php");
require_once($CFG->libdir . '/enrollib.php');
require_once($CFG->libdir . '/filelib.php');

// PayPal does not like when we return error messages here,
// the custom handler just logs exceptions and stops.
set_exception_handler(\enrol_sslcommerz\util::get_exception_handler());

// Make sure we are enabled in the first place.
if (!enrol_is_enabled('sslcommerz')) {
    http_response_code(503);
    throw new moodle_exception('errdisabled', 'enrol_sslcommerz');
}

// Read all the data from PayPal and get it ready for later;
// we expect only valid UTF-8 encoding, it is the responsibility
// of user to set it up properly in PayPal business account
// it is documented in docs wiki.

$req = 'cmd=_notify-validate';


$data = new stdClass();

foreach ($_POST as $key => $value) {
    if ($key !== clean_param($key, PARAM_ALPHANUMEXT)) {
        throw new moodle_exception('invalidrequest', 'core_error', '', null, $key);
    }
    if (is_array($value)) {
        throw new moodle_exception('invalidrequest', 'core_error', '', null, 'Unexpected array param: ' . $key);
    }
    $req .= "&$key=" . urlencode($value);
    $data->$key = fix_utf8($value);
}

if (empty($_POST['value_a'])) {
    throw new moodle_exception('invalidrequest', 'core_error', '', null, 'Missing request param: custom');
}

$custom = explode('-', $_POST['value_a']);


if (empty($custom) || count($custom) < 3) {
    throw new moodle_exception('invalidrequest', 'core_error', '', null, 'Invalid value of the request param: custom');
}


$data->userid = (int)$custom[0];
$data->courseid = (int)$custom[1];
$data->instanceid = (int)$custom[2];
$data->payment_currency = $data->currency;
$data->timeupdated = time();

$user = $DB->get_record("user", array("id" => $data->userid), "*", MUST_EXIST);
$course = $DB->get_record("course", array("id" => $data->courseid), "*", MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

$PAGE->set_context($context);

$plugininstance =
    $DB->get_record("enrol", array("id" => $data->instanceid, "enrol" => "sslcommerz", "status" => 0), "*", MUST_EXIST);
$plugin = enrol_get_plugin('sslcommerz');


// Open a connection back to SSLCommerz to validate the data.


$valid = urlencode($_POST['val_id']);
$storeid = urlencode(get_config('enrol_sslcommerz')->sslstoreid);
$storepasswd = urlencode(get_config('enrol_sslcommerz')->sslstorepassword);
$requestedurl = (get_config("enrol_sslcommerz")->requestedurl . "?val_id=" . $valid . "&store_id=" . $storeid . "&store_passwd=" . $storepasswd . "&v=1&format=json");

$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $requestedurl);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false); // IF YOU RUN FROM LOCAL PC.
curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false); // IF YOU RUN FROM LOCAL PC.

$result = curl_exec($handle);

$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

$result = json_decode($result);


if ($result) {

    if (!empty($SESSION->wantsurl)) {
        $destination = $SESSION->wantsurl;
        unset($SESSION->wantsurl);
    } else {
        $destination = "$CFG->wwwroot/course/view.php?id=$course->id";
    }

    $fullname = format_string($course->fullname, true, array('context' => $context));

    $amount = $_POST['amount'];
    $currency = $_POST['currency'];

    if (empty($_POST['amount']) || empty($_POST['currency'])) {

        $plugin->unenrol_user($plugininstance, $data->userid);
        \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("Invalid Information.",
            $data);
        die;
    }


    $validation = $DB->get_record('enrol_sslcommerz', array('txn_id' => $result->tran_id));

    // Make sure this transaction doesn't exist already.
    if (!$existing = $DB->get_record("enrol_sslcommerz", array("txn_id" => $result->tran_id), "*", IGNORE_MULTIPLE)) {
        \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("Transaction $result->tran_id is being repeated!", $data);
        die;
    }

    if (!$user = $DB->get_record('user', array('id' => $data->userid))) {   // Check that user exists.
        \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("User $data->userid doesn't exist", $data);
        redirect($destination, get_string('usermissing', 'enrol_sslcommerz', $data->userid));
        die;
    }

    if (!$course = $DB->get_record('course', array('id' => $data->courseid))) { // Check that course exists.
        \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("Course $data->courseid doesn't exist", $data);
        redirect($destination, get_string('coursemissing', 'enrol_sslcommerz', $data->courseid));
        die;
    }

    if ((float)$plugininstance->cost <= 0) {
        $cost = (float)$plugin->get_config('cost');
    } else {
        $cost = (float)$plugininstance->cost;
    }

    // Use the same rounding of floats as on the enrol form.
    $cost = format_float($cost, 2, false);

    if ($result->amount < $cost) {
        \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("Amount paid is not enough ($data->payment_gross < $cost))",
            $data);
        redirect($destination, get_string('paymendue', 'enrol_sslcommerz', $result->amount));
        die;
    }

    // Use the queried course's full name for the item_name field.
    $data->item_name = $course->fullname;
    $data->payment_status = $result->status;

    $coursecontext = context_course::instance($course->id, IGNORE_MISSING);

    switch ($result->status) {
        case 'VALID':
            // Check from existing record.
            if ($validation->payment_status == 'Pending') {

                $data->id = $validation->id;
                $data->payment_status = 'Pending';
                $entry = $DB->update_record("enrol_sslcommerz", $data, $bulk = false);
                $DB->insert_record("enrol_sslcommerz_log", $data, $bulk = false);

                if ($plugininstance->enrolperiod) {
                    $timestart = time();
                    $timeend = $timestart + $plugininstance->enrolperiod;
                } else {
                    $timestart = 0;
                    $timeend = 0;
                }

                // Enrol user.
                $plugin->enrol_user($plugininstance, $user->id, $plugininstance->roleid, $timestart, $timeend);


                $this->mailFuntion($context);

                $fullname = format_string($course->fullname, true, array('context' => $context));

                if (is_enrolled($context, $user, '', true)) { // TODO: use real sslcommerz check.
                    echo "Payment Successful";

                } else {   // Somehow they aren't enrolled yet.
                    echo "Payment was not valid";
                }
            } else {
                echo "This order is already Successful";
            }

            break;

        case 'FAILED':

            $data->id = $validation->id;
            $data->payment_status = 'Processing';
            $entry = $DB->update_record("enrol_sslcommerz", $data, $bulk = false);
            $DB->insert_record("enrol_sslcommerz_log", $data, $bulk = false);
            redirect($destination, get_string('paymentfail', 'enrol_sslcommerz', $fullname));

            break;

        case 'CANCELLED':

            $record = $DB->update_record("enrol_sslcommerz", $data, $bulk = false);
            $log = $DB->insert_record("enrol_sslcommerz_log", $data);
            echo "Payment was Cancelled";

            break;

        default:

            $record = $DB->update_record("enrol_sslcommerz", $data, $bulk = false);
            $log = $DB->insert_record("enrol_sslcommerz_log", $data);
            echo "Invalid Information.";

            break;
    }

}
