<?php
//This file is part of Moodle - http://moodle.org/
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


require_once("../../config.php");


use SslCommerz\SslCommerzNotification;
global $DB, $USER, $OUTPUT, $CFG, $PAGE;

require_once(__DIR__ . "/configurations/lib/SslCommerzNotification.php");
include_once(__DIR__ . "/configurations/OrderTransaction.php");


    $instanceid = $_POST['value_d'];
    $userid = $_POST['value_a'];
    $courseid =  $_POST['value_c'];
    $currency_type = $_POST['currency_type'];

    $plugin = enrol_get_plugin('sslcommerz');
    $plugininstance= $DB->get_record("enrol", array("id" => $instanceid, "enrol" => "sslcommerz", "status" => 0));
    $plugin->enrol_user($plugininstance, $userid, $plugininstance->roleid);


    $tran_id        = required_param('tran_id', PARAM_TEXT);

    $sslc = new SslCommerzNotification();
    $ot = new OrderTransaction();
    $sql = $ot->getRecordQuery($tran_id);
    $row = $DB->get_record_sql($sql);

    if ($row->payment_status == 'Pending' || $row->payment_status == 'Processing') {
        $sql = $ot->updateTransactionQuery($tran_id, 'Processing', $currency_type);
        $DB->execute($sql);
        $url = new moodle_url('/course/view.php?id='. $courseid);
        redirect($url);

    }


