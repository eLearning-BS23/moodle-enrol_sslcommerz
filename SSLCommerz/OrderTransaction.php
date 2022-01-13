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


class OrderTransaction {

    public function getRecordQuery($tran_id)
    {
        $sql = "select * from {enrol_sslcommerz} WHERE txn_id ='" . $tran_id . "'";
        return $sql;
    }


    public function saveTransactionQuery($post_data)
    {

        $transaction_id = $post_data['tran_id'];
        $userid = $post_data['userid'];
        $courseid = $post_data['value_c'];
        $instance_id = $post_data['value_d'];

        $sql = "INSERT INTO {enrol_sslcommerz} (userid, courseid, instanceid, payment_status, txn_id)
                                    VALUES ($userid,$courseid, $instance_id, 'Pending', '$transaction_id')";
        return $sql;
    }

    public function logTransactionQuery($post_data)
    {

        $transaction_id = $post_data['tran_id'];
        $userid = $post_data['userid'];
        $courseid = $post_data['value_c'];
        $instance_id = $post_data['value_d'];

        $sql = "INSERT INTO {enrol_sslcommerz_log} (userid, courseid, instanceid, payment_status, txn_id)
                                    VALUES ($userid,$courseid, $instance_id, 'Pending', '$transaction_id')";
        return $sql;
    }

    public function updateTransactionQuery($tran_id, $type = 'Success', $currency_type)
    {

        $sql = "UPDATE {enrol_sslcommerz} SET payment_status='$type', payment_type='$currency_type' WHERE txn_id ='$tran_id'";

        return $sql;
    }
}

