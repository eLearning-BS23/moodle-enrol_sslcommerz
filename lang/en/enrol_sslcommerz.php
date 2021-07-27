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
 * Strings for component 'enrol_sslcommerz', language 'en'.
 *
 * @package    enrol_sslcommerz
 * @copyright  2021 Brain station 23 ltd.
 * @author     Brain station 23 ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['assignrole'] = 'Assign role';
$string['businessemail'] = 'sslcommerz business email';
$string['businessemail_desc'] = 'The email address of your business sslcommerz account';
$string['cost'] = 'Enrol cost';
$string['costerror'] = 'The enrolment cost is not numeric';
$string['costorkey'] = 'Please choose one of the following methods of enrolment.';
$string['currency'] = 'Currency';
$string['defaultrole'] = 'Default role assignment';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during sslcommerz enrolments';
$string['enrolenddate'] = 'End date';
$string['enrolenddate_help'] = 'If enabled, users can be enrolled until this date only.';
$string['enrolenddaterror'] = 'Enrolment end date cannot be earlier than start date';
$string['enrolperiod'] = 'Enrolment duration';
$string['enrolperiod_desc'] = 'Default length of time that the enrolment is valid. If set to zero, the enrolment duration will be unlimited by default.';
$string['enrolperiod_help'] = 'Length of time that the enrolment is valid, starting with the moment the user is enrolled. If disabled, the enrolment duration will be unlimited.';
$string['enrolstartdate'] = 'Start date';
$string['enrolstartdate_help'] = 'If enabled, users can be enrolled from this date onward only.';
$string['errdisabled'] = 'The sslcommerz enrolment plugin is disabled and does not handle payment notifications.';
$string['erripninvalid'] = 'Instant payment notification has not been verified by sslcommerz.';
$string['errsslcommerzconnect'] = 'Could not connect to {$a->url} to verify the instant payment notification: {$a->result}';
$string['expiredaction'] = 'Enrolment expiry action';
$string['expiredaction_help'] = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['mailadmins'] = 'Notify admin';
$string['mailstudents'] = 'Notify students';
$string['mailteachers'] = 'Notify teachers';
$string['messageprovider:enrol_sslcommerz'] = 'Sslcommerz enrolment messages';
$string['nocost'] = 'There is no cost associated with enrolling in this course!';
$string['sslcommerz:config'] = 'Configure sslcommerz enrol instances';
$string['sslcommerz:manage'] = 'Manage enrolled users';
$string['sslcommerz:unenrol'] = 'Unenrol users from course';
$string['sslcommerz:unenrolself'] = 'Unenrol self from the course';
$string['sslcommerzaccepted'] = 'sslcommerz payments accepted';
$string['pluginname'] = 'sslcommerz';
$string['pluginname_desc'] = 'The sslcommerz module allows you to set up paid courses.  If the cost for any course is zero, then students are not asked to pay for entry.  There is a site-wide cost that you set here as a default for the whole site and then a course setting that you can set for each course individually. The course cost overrides the site cost.';
$string['privacy:metadata:enrol_sslcommerz:enrol_sslcommerz'] = 'Information about the sslcommerz transactions for sslcommerz enrolments.';
$string['privacy:metadata:enrol_sslcommerz:enrol_sslcommerz:business'] = 'Email address or sslcommerz account ID of the payment recipient (that is, the merchant).';
$string['privacy:metadata:enrol_sslcommerz:enrol_sslcommerz:courseid'] = 'The ID of the course that is sold.';
$string['privacy:metadata:enrol_sslcommerz:enrol_sslcommerz:instanceid'] = 'The ID of the enrolment instance in the course.';
$string['privacy:metadata:enrol_sslcommerz:enrol_sslcommerz:item_name'] = 'The full name of the course that its enrolment has been sold.';
$string['privacy:metadata:enrol_sslcommerz:enrol_sslcommerz:memo'] = 'A note that was entered by the buyer in sslcommerz website payments note field.';
$string['privacy:metadata:enrol_sslcommerz:enrol_sslcommerz:option_selection1_x'] = 'Full name of the buyer.';
$string['privacy:metadata:enrol_sslcommerz:enrol_sslcommerz:parent_txn_id'] = 'In the case of a refund, reversal, or canceled reversal, this would be the transaction ID of the original transaction.';
$string['privacy:metadata:enrol_sslcommerz:enrol_sslcommerz:payment_status'] = 'The status of the payment.';
$string['privacy:metadata:enrol_sslcommerz:enrol_sslcommerz:payment_type'] = 'Holds whether the payment was funded with an eCheck (echeck), or was funded with sslcommerz balance, credit card, or instant transfer (instant).';
$string['privacy:metadata:enrol_sslcommerz:enrol_sslcommerz:pending_reason'] = 'The reason why payment status is pending (if that is).';
$string['privacy:metadata:enrol_sslcommerz:enrol_sslcommerz:reason_code'] = 'The reason why payment status is Reversed, Refunded, Canceled_Reversal, or Denied (if the status is one of them).';
$string['privacy:metadata:enrol_sslcommerz:enrol_sslcommerz:receiver_email'] = 'Primary email address of the payment recipient (that is, the merchant).';
$string['privacy:metadata:enrol_sslcommerz:enrol_sslcommerz:receiver_id'] = 'Unique sslcommerz account ID of the payment recipient (i.e., the merchant).';
$string['privacy:metadata:enrol_sslcommerz:enrol_sslcommerz:tax'] = 'Amount of tax charged on payment.';
$string['privacy:metadata:enrol_sslcommerz:enrol_sslcommerz:timeupdated'] = 'The time of Moodle being notified by sslcommerz about the payment.';
$string['privacy:metadata:enrol_sslcommerz:enrol_sslcommerz:txn_id'] = 'The merchant\'s original transaction identification number for the payment from the buyer, against which the case was registered';
$string['privacy:metadata:enrol_sslcommerz:enrol_sslcommerz:userid'] = 'The ID of the user who bought the course enrolment.';
$string['privacy:metadata:enrol_sslcommerz:sslcommerz_com'] = 'The sslcommerz enrolment plugin transmits user data from Moodle to the sslcommerz website.';
$string['privacy:metadata:enrol_sslcommerz:sslcommerz_com:address'] = 'Address of the user who is buying the course.';
$string['privacy:metadata:enrol_sslcommerz:sslcommerz_com:city'] = 'City of the user who is buying the course.';
$string['privacy:metadata:enrol_sslcommerz:sslcommerz_com:country'] = 'Country of the user who is buying the course.';
$string['privacy:metadata:enrol_sslcommerz:sslcommerz_com:custom'] = 'A hyphen-separated string that contains ID of the user (the buyer), ID of the course, ID of the enrolment instance.';
$string['privacy:metadata:enrol_sslcommerz:sslcommerz_com:email'] = 'Email address of the user who is buying the course.';
$string['privacy:metadata:enrol_sslcommerz:sslcommerz_com:first_name'] = 'First name of the user who is buying the course.';
$string['privacy:metadata:enrol_sslcommerz:sslcommerz_com:last_name'] = 'Last name of the user who is buying the course.';
$string['privacy:metadata:enrol_sslcommerz:sslcommerz_com:os0'] = 'Full name of the buyer.';
$string['processexpirationstask'] = 'sslcommerz enrolment send expiry notifications task';
$string['sendpaymentbutton'] = 'Send payment via sslcommerz';
$string['status'] = 'Allow sslcommerz enrolments';
$string['status_desc'] = 'Allow users to use sslcommerz to enrol into a course by default.';
$string['transactions'] = 'sslcommerz transactions';
$string['unenrolselfconfirm'] = 'Do you really want to unenrol yourself from course "{$a}"?';
