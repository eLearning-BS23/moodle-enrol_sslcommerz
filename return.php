<?php

use mod_lti\local\ltiservice\response;

require("../../config.php");
require_once("$CFG->dirroot/enrol/sslcommerz/lib.php");

global $CFG, $USER;
/* PHP */

$val_id=urlencode($_POST['val_id']);
$store_id=urlencode(get_config('enrol_sslcommerz')->sslstoreid);
$store_passwd=urlencode(get_config('enrol_sslcommerz')->sslstorepassword);
$requested_url = ("https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php?val_id=".$val_id."&store_id=".$store_id."&store_passwd=".$store_passwd."&v=1&format=json");
$id = required_param('id', PARAM_INT);
$userId = required_param('user_id', PARAM_INT);
$instanceId = required_param('instance', PARAM_INT);

$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $requested_url);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false); # IF YOU RUN FROM LOCAL PC
curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false); # IF YOU RUN FROM LOCAL PC

$result = curl_exec($handle);

$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

if($code == 200 && !( curl_errno($handle)))
{
	# TO CONVERT AS ARRAY
	# $result = json_decode($result, true);
	# $status = $result['status'];

	# TO CONVERT AS OBJECT
	$result = json_decode($result);

	# TRANSACTION INFO
	$status = $result->status;
	$tran_date = $result->tran_date;
	$tran_id = $result->tran_id;
	$val_id = $result->val_id;
	$amount = $result->amount;
	$store_amount = $result->store_amount;
	$bank_tran_id = $result->bank_tran_id;
	$card_type = $result->card_type;

	# EMI INFO
	// $emi_ instalment = $result->emi_instalment;
	// $emi_ amount = $result->emi_ amount;
	// $emi_description = $result->emi_description;
	// $emi_issuer = $result->emi_issuer;

	# ISSUER INFO
	$card_no = $result->card_no;
	$card_issuer = $result->card_issuer;
	$card_brand = $result->card_brand;
	$card_issuer_country = $result->card_issuer_country;
	$card_issuer_country_code = $result->card_issuer_country_code;

	# API AUTHENTICATION
	$APIConnect = $result->APIConnect;
	$validated_on = $result->validated_on;
	$gw_version = $result->gw_version;

// ALL CLEAR !

    $data = new stdClass();


    foreach ($_POST as $key => $value) {
        if ($key !== clean_param($key, PARAM_ALPHANUMEXT)) {
            throw new moodle_exception('invalidrequest', 'core_error', '', null, $key);
        }
        if (is_array($value)) {
            throw new moodle_exception('invalidrequest', 'core_error', '', null, 'Unexpected array param: '.$key);
        }
        $req .= "&$key=".urlencode($value);
        $data->$key = fix_utf8($value);
    }
    $user = $DB->get_record("user", array("id" => $userId), "*", MUST_EXIST);
    $course = $DB->get_record("course", array("id" => $id), "*", MUST_EXIST);

    $data->receiver_email   = $user->email;
    $data->memo             = $_POST['tran_id'];
    $data->txn_id           = $_POST['tran_id'];
    $data->payment_type     = $card_type;
    $data->payment_status   = $status;
    $data->userid           = (int)$userId;
    $data->courseid         = (int)$id;
    $data->instanceid       = (int)$instanceId;
    $data->timeupdated      = time();


    $course = $DB->get_record("course", array("id" => $data->courseid), "*", MUST_EXIST);
    $context = context_course::instance($course->id, MUST_EXIST);

    $PAGE->set_context($context);

    $plugin_instance = $DB->get_record("enrol", array("id" => $data->instanceid, "enrol" => "sslcommerz", "status" => 0), "*", MUST_EXIST);

    $plugin = enrol_get_plugin('sslcommerz');


    if (strcmp($status, "VALIDATED") == 0) {          // VALID PAYMENT!

        // check the payment_status and payment_reason

        // If status is not completed or pending then unenrol the student if already enrolled
        // and notify admin

        // If currency is incorrectly set then someone maybe trying to cheat the system

//        if ($data->mc_currency != $plugin_instance->currency) {
//            \enrol_sslcommerz\util::message_sslcommerz_error_to_admin(
//                "Currency does not match course settings, received: ".$data->mc_currency,
//                $data);
//            die;
//        }

        // If status is pending and reason is other than echeck then we are on hold until further notice
        // Email user to let them know. Email admin.

        if ($data->payment_status == "Pending" and $data->pending_reason != "echeck") {
            $eventdata = new \core\message\message();
            $eventdata->courseid          = empty($data->courseid) ? SITEID : $data->courseid;
            $eventdata->modulename        = 'moodle';
            $eventdata->component         = 'enrol_sslcommerz';
            $eventdata->name              = 'sslcommerz_enrolment';
            $eventdata->userfrom          = get_admin();
            $eventdata->userto            = $user;
            $eventdata->subject           = "Moodle: sslcommerz payment";
            $eventdata->fullmessage       = "Your sslcommerz payment is pending.";
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';
            $eventdata->smallmessage      = '';
            message_send($eventdata);

            \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("Payment pending", $data);
            die;
        }

        // If our status is not completed or not pending on an echeck clearance then ignore and die
        // This check is redundant at present but may be useful if sslcommerz extend the return codes in the future

//        if (! ( $data->payment_status == "Completed" or
//            ($data->payment_status == "Pending" and $data->pending_reason == "echeck") ) ) {
//            die;
//        }

        // At this point we only proceed with a status of completed or pending with a reason of echeck

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

//        if (core_text::strtolower($recipient) !== core_text::strtolower($plugin->get_config('sslcommerzbusiness'))) {
//            \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("Business email is {$recipient} (not ".
//                $plugin->get_config('sslcommerzbusiness').")", $data);
//            die;
//        }

        if (!$user = $DB->get_record('user', array('id'=>$data->userid))) {   // Check that user exists
            \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("User $data->userid doesn't exist", $data);
            die;
        }

        if (!$course = $DB->get_record('course', array('id'=>$data->courseid))) { // Check that course exists
            \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("Course $data->courseid doesn't exist", $data);
            die;
        }

        $coursecontext = context_course::instance($course->id, IGNORE_MISSING);

        // Check that amount paid is the correct amount
        if ( (float) $plugin_instance->cost <= 0 ) {
            $cost = (float) $plugin->get_config('cost');
        } else {
            $cost = (float) $plugin_instance->cost;
        }

        // Use the same rounding of floats as on the enrol form.
        $cost = format_float($cost, 2, false);

//        if ($data->payment_gross < $cost) {
//            \enrol_sslcommerz\util::message_sslcommerz_error_to_admin("Amount paid is not enough ($data->payment_gross < $cost))", $data);
//            die;
//
//        }
        // Use the queried course's full name for the item_name field.
        $data->item_name = $course->fullname;

        // ALL CLEAR !

        $DB->insert_record("enrol_sslcommerz", $data);
        if ($plugin_instance->enrolperiod) {
            $timestart = time();
            $timeend   = $timestart + $plugin_instance->enrolperiod;
        } else {
            $timestart = 0;
            $timeend   = 0;
        }

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
        $mailadmins   = $plugin->get_config('mailadmins');
        $shortname = format_string($course->shortname, true, array('context' => $context));


        if (!empty($mailstudents)) {
            $a = new stdClass();
            $a->coursename = format_string($course->fullname, true, array('context' => $coursecontext));
            $a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id";

            $eventdata = new \core\message\message();
            $eventdata->courseid          = $course->id;
            $eventdata->modulename        = 'moodle';
            $eventdata->component         = 'enrol_sslcommerz';
            $eventdata->name              = 'sslcommerz_enrolment';
            $eventdata->userfrom          = empty($teacher) ? core_user::get_noreply_user() : $teacher;
            $eventdata->userto            = $user;
            $eventdata->subject           = get_string("enrolmentnew", 'enrol', $shortname);
            $eventdata->fullmessage       = get_string('welcometocoursetext', '', $a);
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';
            $eventdata->smallmessage      = '';
            message_send($eventdata);

        }

        if (!empty($mailteachers) && !empty($teacher)) {
            $a->course = format_string($course->fullname, true, array('context' => $coursecontext));
            $a->user = fullname($user);

            $eventdata = new \core\message\message();
            $eventdata->courseid          = $course->id;
            $eventdata->modulename        = 'moodle';
            $eventdata->component         = 'enrol_sslcommerz';
            $eventdata->name              = 'sslcommerz_enrolment';
            $eventdata->userfrom          = $user;
            $eventdata->userto            = $teacher;
            $eventdata->subject           = get_string("enrolmentnew", 'enrol', $shortname);
            $eventdata->fullmessage       = get_string('enrolmentnewuser', 'enrol', $a);
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';
            $eventdata->smallmessage      = '';
            message_send($eventdata);
        }

        if (!empty($mailadmins)) {
            $a->course = format_string($course->fullname, true, array('context' => $coursecontext));
            $a->user = fullname($user);
            $admins = get_admins();
            foreach ($admins as $admin) {
                $eventdata = new \core\message\message();
                $eventdata->courseid          = $course->id;
                $eventdata->modulename        = 'moodle';
                $eventdata->component         = 'enrol_sslcommerz';
                $eventdata->name              = 'sslcommerz_enrolment';
                $eventdata->userfrom          = $user;
                $eventdata->userto            = $admin;
                $eventdata->subject           = get_string("enrolmentnew", 'enrol', $shortname);
                $eventdata->fullmessage       = get_string('enrolmentnewuser', 'enrol', $a);
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml   = '';
                $eventdata->smallmessage      = '';
                message_send($eventdata);
            }
        }
        $url = $CFG->wwwroot."/enrol/sslcommerz/success.php?id=".$_POST['course_id'];
        redirect($url, 'optional message', 10);
    }
}
else {
	echo "Failed to connect with SSLCOMMERZ";
}


