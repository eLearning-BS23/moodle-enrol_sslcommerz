<?php
// This file is part of the Zoom plugin for Moodle - http://moodle.org/
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
 * AWS webgl module version info
 *
 * @package mod_webgl
 * @copyright  2020 Brain station 23 ltd <>  {@link https://brainstation-23.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');


$courseid = required_param('id', PARAM_INT);

$course = $DB->get_record("course", array("id" => $courseid), "*", MUST_EXIST);
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
$PAGE->set_title($course->shortname . ': ' . get_string('pluginname', 'quizaccess_proctoring'));
$PAGE->set_heading($course->fullname . ': ' . get_string('pluginname', 'quizaccess_proctoring'));

$PAGE->navbar->add(get_string('course', 'course'), $url);

echo $OUTPUT->header();


?>
<div class="row" style="margin-top: 10%;">
    <div class="col-md-8 offset-md-2">
        <?php

        // Connect to database after confirming the request
        $tranid = trim($_POST['tran_id']);

        // First check if the POST request is real!
        if (empty($tranid) || empty($tranid)) {
            echo '<h2 class="text-center text-danger">Invalid Information.</h2>';
            exit;
        }


        if ($_POST['status'] == 'PENDING' || $_POST['status'] == 'FAILED') {


            ?>
            <h2 class="text-center text-danger">Unfortunately your Transaction FAILED.</h2>
            <br>

            <table border="1" class="table table-striped">
                <thead class="thead-dark">
                <tr class="text-center">
                    <th colspan="2">Payment Details</th>
                </tr>
                </thead>
                <tr>
                    <td class="text-right">Error</td>
                    <td><?= $_POST['error'] ?></td>
                </tr>
                <tr>
                    <td class="text-right">Transaction ID</td>
                    <td><?= $tranid ?></td>
                </tr>
                <tr>
                    <td class="text-right">Payment Method</td>
                    <td><?= $_POST['card_issuer'] ?></td>
                </tr>
                <?php if ($_POST['bank_tran_id']) { ?>
                    <tr>
                        <td class="text-right">Bank Transaction Id</td>
                        <td><?= $_POST['bank_tran_id'] ?></td>
                    </tr>
                <?php }
                ?>

                <tr>
                    <td class="text-right"><b>Amount: </b></td>
                    <td><?= $_POST['amount'] . ' ' . $_POST['currency'] ?></td>
                </tr>
            </table>
            <h2 class="text-center text-danger">Error updating record: </h2> <?php echo $_POST['error']; ?>
        <?php } ?>
        <?php if ($_POST['status'] == 'PROCESSING') : ?>
            <table border="1" class="table table-striped">
                <thead class="thead-dark">
                <tr class="text-center">
                    <th colspan="2">Payment Details</th>
                </tr>
                </thead>
                <tr>
                    <td class="text-right">Transaction ID</td>
                    <td><?= $tranid ?></td>
                </tr>
                <tr>
                    <td class="text-right">Transaction Time</td>
                    <td><?= $_POST['tran_date'] ?></td>
                </tr>
                <tr>
                    <td class="text-right">Payment Method</td>
                    <td><?= $_POST['card_issuer'] ?></td>
                </tr>
                <tr>
                    <td class="text-right">Bank Transaction ID</td>
                    <td><?= $_POST['bank_tran_id'] ?></td>
                </tr>
                <tr>
                    <td class="text-right">Amount</td>
                    <td><?= $_POST['amount'] . ' ' . $_POST['currency'] ?></td>
                </tr>
            </table>
        <?php endif ?>
    </div>
