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
// along with Moodle.  If not, see <http://www.gnu.org/license

/**
 * Listens for Instant Payment Notification from PayPal
 *
 * This script waits for Payment notification from PayPal,
 * then double checks that data by sending it back to PayPal.
 * If PayPal verifies this then it sets up the enrolment for that
 * user.
 *
 * @package    enrol_sslcommerz
 * @copyright 2010 Eugene Venter
 * @author     Eugene Venter - based on code by others
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_lti\local\ltiservice\response;

require("../../config.php");
require_once("$CFG->dirroot/enrol/sslcommerz/lib.php");

global $CFG, $USER;

// Disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
//define('NO_DEBUG_DISPLAY', true);

// PayPal does not like when we return error messages here,
// the custom handler just logs exceptions and stops.
set_exception_handler(\enrol_sslcommerz\util::get_exception_handler());

// Make sure we are enabled in the first place.
if (!enrol_is_enabled('sslcommerz')) {
    http_response_code(503);
    throw new moodle_exception('errdisabled', 'enrol_sslcommerz');
}

/// Read all the data from PayPal and get it ready for later;
/// we expect only valid UTF-8 encoding, it is the responsibility
/// of user to set it up properly in PayPal business account,
/// it is documented in docs wiki.

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
$plugin_instance = $DB->get_record("enrol", array("id" => $data->instanceid, "enrol" => "sslcommerz", "status" => 0), "*", MUST_EXIST);
$plugin = enrol_get_plugin('sslcommerz');

/// Open a connection back to SSLCommerz to validate the data
$valid = urlencode($_POST['val_id']);
$storeid = urlencode(get_config('enrol_sslcommerz')->sslstoreid);
$storepasswd = urlencode(get_config('enrol_sslcommerz')->sslstorepassword);
$requested_url = (get_config("enrol_sslcommerz")->requestedurl."?val_id=" . $valid . "&store_id=" . $storeid . "&store_passwd=" . $storepasswd . "&v=1&format=json");

$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $requested_url);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false); # IF YOU RUN FROM LOCAL PC
curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false); # IF YOU RUN FROM LOCAL PC

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
        $plugin->unenrol_user($plugin_instance, $data->userid);
        \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("Invalid Information.",
            $data);
        die;
    }
    $validation = $DB->get_record('enrol_sslcommerz', array('txn_id' => $result->tran_id));
    // Make sure this transaction doesn't exist already.
    if ($existing = $DB->get_record("enrol_sslcommerz", array("txn_id" => $result->txn_id), "*", MUST_EXIST)) {
        \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("Transaction $result->txn_id is being repeated!", $data);
        die;
    }
    if (!$user = $DB->get_record('user', array('id' => $data->userid))) {   // Check that user exists
        \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("User $data->userid doesn't exist", $data);
        redirect($destination, get_string('usermissing', 'enrol_sslcommerz', $data->userid));
        die;
    }
    if (!$course = $DB->get_record('course', array('id' => $data->courseid))) { // Check that course exists
        \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("Course $data->courseid doesn't exist", $data);
        redirect($destination, get_string('coursemissing', 'enrol_sslcommerz', $data->courseid));
        die;
    }
    if ((float)$plugin_instance->cost <= 0) {
        $cost = (float)$plugin->get_config('cost');
    } else {
        $cost = (float)$plugin_instance->cost;
    }
    // Use the same rounding of floats as on the enrol form.
    $cost = format_float($cost, 2, false);
    if ($result->amount < $cost) {
        \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("Amount paid is not enough ($data->payment_gross < $cost))", $data);
        redirect($destination, get_string('paymendue', 'enrol_sslcommerz', $result->amount));
        die;
    }
    // Use the queried course's full name for the item_name field.
    $data->item_name = $course->fullname;
    $data->id = $validation->id;
    $coursecontext = context_course::instance($course->id, IGNORE_MISSING);
    if ($plugin_instance->enrolperiod) {
        $timestart = time();
        $timeend = $timestart + $plugin_instance->enrolperiod;
    } else {
        $timestart = 0;
        $timeend = 0;
    }
    $data->id = $validation->id;
    if ($result->status == 'Pending' || $result->status == 'Processing') {
        if ($validation) {
            $data->payment_status = 'Processing';
            $record = $DB->update_record("enrol_sslcommerz", $data, $bulk = false);
            $log = $DB->insert_record("enrol_sslcommerz_log", $data);
            if ($record) {
                // Enrol user
                $plugin->enrol_user($plugin_instance, $user->id, $plugin_instance->roleid, $timestart, $timeend);
                // Pass $view=true to filter hidden caps if the user cannot see them
                if ($users = get_users_by_capability($context, 'moodle/course:update', 'u.*', 'u.id ASC',
                    '', '', '', '', false, true)) {
                    $users = sort_by_roleassignment_authority($users, $context);
                    $teacher = array_shift($users);
                } else {
                    $teacher = false;
                }
                $mailstudents = $plugin->get_config('mailstudents');
                $mailteachers = $plugin->get_config('mailteachers');
                $mailadmins = $plugin->get_config('mailadmins');
                $shortname = format_string($course->shortname, true, array('context' => $context));
                if (!empty($mailstudents)) {
                    $a = new stdClass();
                    $a->coursename = format_string($course->fullname, true, array('context' => $coursecontext));
                    $a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id";
                    $eventdata = new \core\message\message();
                    $eventdata->courseid = $course->id;
                    $eventdata->modulename = 'moodle';
                    $eventdata->component = 'enrol_sslcommerz';
                    $eventdata->name = 'sslcommerz_enrolment';
                    $eventdata->userfrom = empty($teacher) ? core_user::get_noreply_user() : $teacher;
                    $eventdata->userto = $user;
                    $eventdata->subject = get_string("enrolmentnew", 'enrol', $shortname);
                    $eventdata->fullmessage = get_string('welcometocoursetext', '', $a);
                    $eventdata->fullmessageformat = FORMAT_PLAIN;
                    $eventdata->fullmessagehtml = '';
                    $eventdata->smallmessage = '';
                    message_send($eventdata);
                }
                if (!empty($mailteachers) && !empty($teacher)) {
                    $a->course = format_string($course->fullname, true, array('context' => $coursecontext));
                    $a->user = fullname($user);
                    $eventdata = new \core\message\message();
                    $eventdata->courseid = $course->id;
                    $eventdata->modulename = 'moodle';
                    $eventdata->component = 'enrol_sslcommerz';
                    $eventdata->name = 'sslcommerz_enrolment';
                    $eventdata->userfrom = $user;
                    $eventdata->userto = $teacher;
                    $eventdata->subject = get_string("enrolmentnew", 'enrol', $shortname);
                    $eventdata->fullmessage = get_string('enrolmentnewuser', 'enrol', $a);
                    $eventdata->fullmessageformat = FORMAT_PLAIN;
                    $eventdata->fullmessagehtml = '';
                    $eventdata->smallmessage = '';
                    message_send($eventdata);
                }
                if (!empty($mailadmins)) {
                    $a->course = format_string($course->fullname, true, array('context' => $coursecontext));
                    $a->user = fullname($user);
                    $admins = get_admins();
                    foreach ($admins as $admin) {
                        $eventdata = new \core\message\message();
                        $eventdata->courseid = $course->id;
                        $eventdata->modulename = 'moodle';
                        $eventdata->component = 'enrol_sslcommerz';
                        $eventdata->name = 'sslcommerz_enrolment';
                        $eventdata->userfrom = $user;
                        $eventdata->userto = $admin;
                        $eventdata->subject = get_string("enrolmentnew", 'enrol', $shortname);
                        $eventdata->fullmessage = get_string('enrolmentnewuser', 'enrol', $a);
                        $eventdata->fullmessageformat = FORMAT_PLAIN;
                        $eventdata->fullmessagehtml = '';
                        $eventdata->smallmessage = '';
                        message_send($eventdata);
                    }
                }
                $fullname = format_string($course->fullname, true, array('context' => $context));
                if (is_enrolled($context, $user, '', true)) { // TODO: use real sslcommerz check
                    redirect($destination, get_string('paymentthanks', '', $fullname));
                } else {   /// Somehow they aren't enrolled yet!  :-(
                    $PAGE->set_url($destination);
                    echo $OUTPUT->header();
                    $a = new stdClass();
                    $a->teacher = get_string('defaultcourseteacher');
                    $a->fullname = $fullname;
                    notice(get_string('paymentsorry', '', $a), $destination);
                }
            } else {
                $data->payment_status = 'Conn Error';
                $record = $DB->update_record("enrol_sslcommerz", $data, $bulk = false);
                $log = $DB->insert_record("enrol_sslcommerz_log", $data);
                redirect($destination, get_string('paymenterror', 'enrol_sslcommerz', $fullname));
            }
        } else { // $validated is false
            $data->payment_status = 'Failed';
            $record = $DB->update_record("enrol_sslcommerz", $data, $bulk = false);
            $log = $DB->insert_record("enrol_sslcommerz_log", $data);
            redirect($destination, get_string('paymentfail', 'enrol_sslcommerz', $fullname));
        }
    } else { // status is something else
        $data->payment_status = 'Invalid';
        $record = $DB->update_record("enrol_sslcommerz", $data, $bulk = false);
        $log = $DB->insert_record("enrol_sslcommerz_log", $data);
        redirect($destination, get_string('paymentinvalid', 'enrol_sslcommerz', $fullname));
    }
} else {// ERROR
    $DB->insert_record("enrol_sslcommerz", $data, false);
    throw new moodle_exception('erripninvalid', 'enrol_sslcommerz', '', null, json_encode($data));
}


