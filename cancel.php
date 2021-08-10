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

require_once (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once (dirname(__FILE__) . '/lib.php');


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

$PAGE->navbar->add(get_string('quizaccess_proctoring', 'quizaccess_proctoring'), $url);

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