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

//defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once("$CFG->dirroot/enrol/sslcommerz/lib.php");

global $CFG, $USER;


$courseid = required_param('id', PARAM_INT);

$data = new stdClass();
// check custom data requested from  ssl
if (empty($_POST['value_a'])) {
    throw new moodle_exception('invalidrequest', 'core_error', '', null, 'Missing request param: custom');
}
$custom = explode('-', $_POST['value_a']);
//check custom data is valid
if (empty($custom) || count($custom) < 3) {
    throw new moodle_exception('invalidrequest', 'core_error', '', null, 'Invalid value of the request param: custom');
}
$data->userid = (int)$custom[0];
$data->courseid = (int)$custom[1];
$data->instanceid = (int)$custom[2];
$data->payment_currency = $_POST['currency'];
$data->timeupdated = time();
$data->receiver_email =$USER->email;
$data->receiver_id = $USER->id;
$data->payment_status = $_POST['status'];
$course = $DB->get_record("course", array("id" => $data->courseid), "*", MUST_EXIST);

$data->item_name = $course->fullname;


$validation = $DB->get_record('enrol_sslcommerz', array('txn_id' => $_POST['tran_id']));

$data->id = $validation->id;

$record = $DB->update_record("enrol_sslcommerz", $data, $bulk = false);
$log = $DB->insert_record("enrol_sslcommerz_log", $data);

$context = context_course::instance($course->id, MUST_EXIST);

$PAGE->set_context($context);

//require_login();

$params = array(
    'id' => $courseid
);
$url = new moodle_url(
    '/enrol/index.php',
    $params
);

//$PAGE->set_url($url);
$PAGE->set_pagelayout('course');
$PAGE->set_title($course->shortname . ': ' . get_string('pluginname', 'enrol_sslcommerz'));
$PAGE->set_heading($course->fullname . ': ' . get_string('pluginname', 'enrol_sslcommerz'));

$PAGE->navbar->add(get_string('course', 'enrol_sslcommerz'), $url);

echo $OUTPUT->header();


?>
<div class="row" style="margin-top: 10%;">
    <div class="col-md-8 offset-md-2">
        <?php
        $tranid = trim($_POST['tran_id']);
        // First check if the POST request is real!
        if (empty($tranid) || empty($tranid)) {
            echo '<h2>Invalid Information.</h2>';
            exit;
        }
        if ($_POST['status'] == 'PENDING' || $_POST['status'] == 'CANCELLED') :
            ?>
            <h2 class="text-center text-warning">Transaction has been CANCELLED.</h2>
            <br>

            <table border="1" class="table table-striped">
                <thead class="thead-dark">
                <tr class="text-center">
                    <th colspan="2">Payment Details</th>
                </tr>
                </thead>
                <tr>
                    <td class="text-right">Description</td>
                    <td><?php echo $_POST['error']; ?></td>
                </tr>
                <tr>
                    <td class="text-right">Transaction ID</td>
                    <td><?php echo $tranid; ?></td>
                </tr>
                <tr>
                    <td class="text-right"><b>Amount: </b></td>
                    <td><?php echo $_POST['amount'] . ' ' . $_POST['currency']; ?></td>
                </tr>
            </table>
        <?php endif ?>
    </div>
</div>