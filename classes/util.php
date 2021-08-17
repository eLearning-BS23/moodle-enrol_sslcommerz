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
 * sslcommerz enrolment plugin utility class.
 *
 * @package    enrol_sslcommerz
 * @copyright  2021 Brain station 23 ltd <>  {@link https://brainstation-23.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_sslcommerz;

defined('MOODLE_INTERNAL') || die();

/**
 * sslcommerz enrolment plugin utility class.
 *
 * @package   enrol_sslcommerz
 * @copyright 2021 Brain station 23 ltd <>  {@link https://brainstation-23.com/}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class util {

    /**
     * Alerts site admin of potential problems.
     *
     * @param string $subject email subject
     * @param stdClass $data sslcommerz IPN data
     */
    public static function message_sslcommerz_error_to_admin($subject, $data) {
        $admin = get_admin();
        $site = get_site();

        $message = "$site->fullname:  Transaction failed.\n\n$subject\n\n";

        foreach ($data as $key => $value) {
            $message .= "$key => $value\n";
        }

        $eventdata = new \core\message\message();
        $eventdata->courseid = empty($data->courseid) ? SITEID : $data->courseid;
        $eventdata->modulename = 'moodle';
        $eventdata->component = 'enrol_sslcommerz';
        $eventdata->name = 'sslcommerz_enrolment';
        $eventdata->userfrom = $admin;
        $eventdata->userto = $admin;
        $eventdata->subject = "sslcommerz ERROR: " . $subject;
        $eventdata->fullmessage = $message;
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml = '';
        $eventdata->smallmessage = '';
        message_send($eventdata);
    }

    /**
     * Silent exception handler.
     *
     * @return callable exception handler
     */
    public static function get_exception_handler() {
        return function ($ex) {
            $info = get_exception_info($ex);

            $logerrmsg = "enrol_sslcommerz IPN exception handler: " . $info->message;
            if (debugging('', DEBUG_NORMAL)) {
                $logerrmsg .= ' Debug: ' . $info->debuginfo . "\n" . format_backtrace($info->backtrace, true);
            }
            error_log($logerrmsg);

            if (http_response_code() == 200) {
                http_response_code(500);
            }

            exit(0);
        };
    }
}
