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

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    //--- settings ------------------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_sslcommerz_settings', '', get_string('pluginname_desc', 'enrol_sslcommerz')));

    $settings->add(new admin_setting_configtext('enrol_sslcommerz/sslcommerzbusiness', get_string('businessemail', 'enrol_sslcommerz'), get_string('businessemail_desc', 'enrol_sslcommerz'), '', PARAM_EMAIL));

    $settings->add(new admin_setting_configtext('enrol_sslcommerz/sslstoreid', get_string('storeid', 'enrol_sslcommerz'), get_string('businessstoreid_desc', 'enrol_sslcommerz'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('enrol_sslcommerz/sslstorepassword', get_string('storepassword', 'enrol_sslcommerz'), get_string('businessstorepassword_desc', 'enrol_sslcommerz'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configcheckbox('enrol_sslcommerz/mailstudents', get_string('mailstudents', 'enrol_sslcommerz'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_sslcommerz/mailteachers', get_string('mailteachers', 'enrol_sslcommerz'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_sslcommerz/mailadmins', get_string('mailadmins', 'enrol_sslcommerz'), '', 0));

    // Note: let's reuse the ext sync constants and strings here, internally it is very similar,
    //       it describes what should happen when users are not supposed to be enrolled any more.
    $options = array(
        ENROL_EXT_REMOVED_KEEP           => get_string('extremovedkeep', 'enrol'),
        ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'),
        ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
    );
    $settings->add(new admin_setting_configselect('enrol_sslcommerz/expiredaction', get_string('expiredaction', 'enrol_sslcommerz'), get_string('expiredaction_help', 'enrol_sslcommerz'), ENROL_EXT_REMOVED_SUSPENDNOROLES, $options));

    //--- enrol instance defaults ----------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_sslcommerz_defaults',
        get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));

    $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                     ENROL_INSTANCE_DISABLED => get_string('no'));
    $settings->add(new admin_setting_configselect('enrol_sslcommerz/status',
        get_string('status', 'enrol_sslcommerz'), get_string('status_desc', 'enrol_sslcommerz'), ENROL_INSTANCE_DISABLED, $options));

    $settings->add(new admin_setting_configtext('enrol_sslcommerz/cost', get_string('cost', 'enrol_sslcommerz'), '', 0, PARAM_FLOAT, 4));

    $sslcommerzcurrencies = enrol_get_plugin('sslcommerz')->get_currencies();
    $settings->add(new admin_setting_configselect('enrol_sslcommerz/currency', get_string('currency', 'enrol_sslcommerz'), '', 'USD', $sslcommerzcurrencies));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_sslcommerz/roleid',
            get_string('defaultrole', 'enrol_sslcommerz'),
            get_string('defaultrole_desc', 'enrol_sslcommerz'),
            $student->id ?? null,
            $options));
    }

    $settings->add(new admin_setting_configduration('enrol_sslcommerz/enrolperiod',
        get_string('enrolperiod', 'enrol_sslcommerz'), get_string('enrolperiod_desc', 'enrol_sslcommerz'), 0));
}
