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
require_login($course, true, $cm);
defined('MOODLE_INTERNAL') || die();

require("../../config.php");

use mod_lti\local\ltiservice\response;

global $CFG, $USER;
require_once("$CFG->dirroot/enrol/sslcommerz/lib.php");
/* PHP */
$valid = urlencode($_POST['val_id']);
$storeid = urlencode(get_config('enrol_sslcommerz')->sslstoreid);
$storepasswd = urlencode(get_config('enrol_sslcommerz')->sslstorepassword);
$requestedurl =
    ("https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php?val_id="
        . $valid . "&store_id=" . $storeid . "&store_passwd=" . $storepasswd . "&v=1&format=json");
$id = required_param('id', PARAM_INT);
$userid = required_param('user_id', PARAM_INT);
$instanceid = required_param('instance', PARAM_INT);
$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $requestedurl);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false); // IF YOU RUN FROM LOCAL PC.
curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false); // IF YOU RUN FROM LOCAL PC.
$result = curl_exec($handle);
$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
if ($code == 200 && !(curl_errno($handle))) {
    // TO CONVERT AS ARRAY
    // $result = json_decode($result, true);
    // $status = $result['status'];
    //
    // TO CONVERT AS OBJECT.
    $result = json_decode($result);

    // TRANSACTION INFO.
    $status = $result->status;
    $trandate = $result->tran_date;
    $tranid = $result->tran_id;
    $valid = $result->val_id;
    $amount = $result->amount;
    $storeamount = $result->store_amount;
    $banktranid = $result->bank_tran_id;
    $cardtype = $result->card_type;

    // EMI INFO
    // ... $emi_ instalment = $result->emi_instalment;.
    // ... $emi_ amount = $result->emi_ amount;.
    // ... $emi_description = $result->emi_description;.
    // ... $emi_issuer = $result->emi_issuer;.

    // ISSUER INFO
    $cardno = $result->card_no;
    $cardissuer = $result->card_issuer;
    $cardbrand = $result->card_brand;
    $cardissuercountry = $result->card_issuer_country;
    $cardissuercountrycode = $result->card_issuer_country_code;

    // API AUTHENTICATION.
    $apiconnect = $result->APIConnect;
    $validatedon = $result->validated_on;
    $gwversion = $result->gw_version;

    // ALL CLEAR.

    $data = new stdClass();

    // TODO: test foreach.
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
    $user = $DB->get_record("user", array("id" => $userid), "*", MUST_EXIST);
    $course = $DB->get_record("course", array("id" => $id), "*", MUST_EXIST);

    $data->receiver_email = $user->email;
    $data->memo = $_POST['tran_id'];
    $data->txn_id = $_POST['tran_id'];
    $data->payment_type = $cardtype;
    $data->payment_status = $status;
    $data->userid = (int)$userid;
    $data->courseid = (int)$id;
    $data->instanceid = (int)$instanceid;
    $data->mc_currency = $_POST['currency_type'];
    $data->timeupdated = time();


    $course = $DB->get_record("course", array("id" => $data->courseid), "*", MUST_EXIST);
    $context = context_course::instance($course->id, MUST_EXIST);

    $PAGE->set_context($context);

    $plugininstance =
        $DB->get_record("enrol", array("id" => $data->instanceid, "enrol" => "sslcommerz", "status" => 0), "*", MUST_EXIST);

    $plugin = enrol_get_plugin('sslcommerz');

    if (strcmp($status, "VALID") == 0) {          // VALID PAYMENT.

        // Check the payment_status and payment_reason.

        // Make sure this transaction doesn't exist already.
        if ($existing = $DB->get_record("enrol_sslcommerz", array("txn_id" => $data->txn_id), "*", IGNORE_MULTIPLE)) {
            \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("Transaction $data->txn_id is being repeated!", $data);
            die;
        }

        // Check that the receiver email is the one we want it to be.
        if (isset($data->business)) {
            $recipient = $data->business;
        } else if (isset($data->receiver_email)) {
            $recipient = $data->receiver_email;
        } else {
            $recipient = 'empty';
        }

        /*
        if (core_text::strtolower($recipient) !== core_text::strtolower($plugin->get_config('sslcommerzbusiness'))) {
            \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("Business email is {$recipient} (not ".
                $plugin->get_config('sslcommerzbusiness').")", $data);
            die;
        }
        */

        if (!$user = $DB->get_record('user', array('id' => $data->userid))) {   // Check that user exists.
            \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("User $data->userid doesn't exist", $data);
            die;
        }

        if (!$course = $DB->get_record('course', array('id' => $data->courseid))) { // Check that course exists.
            \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("Course $data->courseid doesn't exist", $data);
            die;
        }

        $coursecontext = context_course::instance($course->id, IGNORE_MISSING);

        // Check that amount paid is the correct amount.
        if ((float)$plugininstance->cost <= 0) {
            $cost = (float)$plugin->get_config('cost');
        } else {
            $cost = (float)$plugininstance->cost;
        }

        // Use the same rounding of floats as on the enrol form.
        // Use the queried course's full name for the item_name field.
        $data->item_name = $course->fullname;

        // ALL CLEAR.

        $cost = format_float($cost, 2, false);

        $DB->insert_record("enrol_sslcommerz", $data);

        if ($plugininstance->enrolperiod) {
            $timestart = time();
            $timeend = $timestart + $plugininstance->enrolperiod;
        } else {
            $timestart = 0;
            $timeend = 0;
        }

        // Enrol user.
        $plugin->enrol_user($plugininstance, $user->id, $plugininstance->roleid, $timestart, $timeend);

        // Pass $view=true to filter hidden caps if the user cannot see them.
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


        if (!empty($SESSION->wantsurl)) {
            $destination = $SESSION->wantsurl;
            unset($SESSION->wantsurl);
        } else {
            $destination = "$CFG->wwwroot/course/view.php?id=$course->id";
        }

        $fullname = format_string($course->fullname, true, array('context' => $context));

        redirect($destination, get_string('paymentthanks', '', $fullname));

    }
} else {
    echo "Failed to connect with SSLCOMMERZ";
}
