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

/*
 * sslcommerz enrolment plugin tests.
 *
 * @package    enrol_sslcommerz
 * @copyright  2021 Brain station 23 ltd.
 * @author     Brain station 23 ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * SSLCOMMERZ enrolment plugin tests.
 *
 * @copyright  2021 Brain station 23 ltd.
 * @author     Brain station 23 ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sslcommerz_test extends advanced_testcase
{
    public function test_basics()
    {
        $this->assertFalse(enrol_is_enabled('sslcommerz'));
        $plugin = enrol_get_plugin('sslcommerz');
        $this->assertInstanceOf('enrol_sslcommerz_plugin', $plugin);
        $this->assertEquals(ENROL_EXT_REMOVED_SUSPENDNOROLES, get_config('enrol_sslcommerz', 'expiredaction'));
    }

    public function test_sync_nothing()
    {
        $this->resetAfterTest();

        $this->enable_plugin();
        $sslcommerzplugin = enrol_get_plugin('sslcommerz');

        // Just make sure the sync does not throw any errors when nothing to do.
        $sslcommerzplugin->sync(new null_progress_trace());
    }

    /**
     * Enable Plugin.
     *
     * @return void
     */
    protected function enable_plugin()
    {
        $enabled = enrol_get_plugins(true);
        $enabled['sslcommerz'] = true;
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }

    public function test_expired()
    {
        global $DB;
        $this->resetAfterTest();

        /** @var enrol_sslcommerz_plugin $sslcommerzplugin */
        $sslcommerzplugin = enrol_get_plugin('sslcommerz');
        /** @var enrol_manual_plugin $manualplugin */
        $manualplugin = enrol_get_plugin('manual');
        $this->assertNotEmpty($manualplugin);

        $now = time();
        $trace = new null_progress_trace();
        $this->enable_plugin();

        // Prepare some data.

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $this->assertNotEmpty($studentrole);
        $teacherrole = $DB->get_record('role', ['shortname' => 'teacher']);
        $this->assertNotEmpty($teacherrole);
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->assertNotEmpty($managerrole);

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $context1 = context_course::instance($course1->id);
        $context2 = context_course::instance($course2->id);

        $data = ['roleid' => $studentrole->id, 'courseid' => $course1->id];
        $id = $sslcommerzplugin->add_instance($course1, $data);
        $instance1 = $DB->get_record('enrol', ['id' => $id]);
        $data = ['roleid' => $studentrole->id, 'courseid' => $course2->id];
        $id = $sslcommerzplugin->add_instance($course2, $data);
        $instance2 = $DB->get_record('enrol', ['id' => $id]);
        $data = ['roleid' => $teacherrole->id, 'courseid' => $course2->id];
        $id = $sslcommerzplugin->add_instance($course2, $data);
        $instance3 = $DB->get_record('enrol', ['id' => $id]);

        $maninstance1 = $DB->get_record('enrol', ['courseid' => $course2->id, 'enrol' => 'manual'], '*', MUST_EXIST);

        $manualplugin->enrol_user($maninstance1, $user3->id, $studentrole->id);

        $this->assertEquals(1, $DB->count_records('user_enrolments'));
        $this->assertEquals(1, $DB->count_records('role_assignments'));
        $this->assertEquals(1, $DB->count_records('role_assignments', ['roleid' => $studentrole->id]));

        $sslcommerzplugin->enrol_user($instance1, $user1->id, $studentrole->id);
        $sslcommerzplugin->enrol_user($instance1, $user2->id, $studentrole->id);
        $sslcommerzplugin->enrol_user($instance1, $user3->id, $studentrole->id, 0, $now - 60);

        $sslcommerzplugin->enrol_user($instance2, $user1->id, $studentrole->id, 0, 0);
        $sslcommerzplugin->enrol_user($instance2, $user2->id, $studentrole->id, 0, $now - 60 * 60);
        $sslcommerzplugin->enrol_user($instance2, $user3->id, $studentrole->id, 0, $now + 60 * 60);

        $sslcommerzplugin->enrol_user($instance3, $user1->id, $teacherrole->id, $now - 60 * 60 * 24 * 7, $now - 60);
        $sslcommerzplugin->enrol_user($instance3, $user4->id, $teacherrole->id);

        role_assign($managerrole->id, $user3->id, $context1->id);

        $this->assertEquals(9, $DB->count_records('user_enrolments'));
        $this->assertEquals(9, $DB->count_records('role_assignments'));
        $this->assertEquals(6, $DB->count_records('role_assignments', ['roleid' => $studentrole->id]));
        $this->assertEquals(2, $DB->count_records('role_assignments', ['roleid' => $teacherrole->id]));
        $this->assertEquals(1, $DB->count_records('role_assignments', ['roleid' => $managerrole->id]));

        // Execute tests.

        $sslcommerzplugin->set_config('expiredaction', ENROL_EXT_REMOVED_KEEP);
        $code = $sslcommerzplugin->sync($trace);
        $this->assertSame(0, $code);
        $this->assertEquals(9, $DB->count_records('user_enrolments'));
        $this->assertEquals(9, $DB->count_records('role_assignments'));

        $sslcommerzplugin->set_config('expiredaction', ENROL_EXT_REMOVED_SUSPENDNOROLES);
        $sslcommerzplugin->sync($trace);
        $this->assertEquals(9, $DB->count_records('user_enrolments'));
        $this->assertEquals(6, $DB->count_records('role_assignments'));
        $this->assertEquals(4, $DB->count_records('role_assignments',
            ['roleid' => $studentrole->id]));
        $this->assertEquals(1, $DB->count_records('role_assignments',
            ['roleid' => $teacherrole->id]));
        $this->assertFalse($DB->record_exists('role_assignments',
            ['contextid' => $context1->id, 'userid' => $user3->id, 'roleid' => $studentrole->id]));
        $this->assertFalse($DB->record_exists('role_assignments',
            ['contextid' => $context2->id, 'userid' => $user2->id, 'roleid' => $studentrole->id]));
        $this->assertFalse($DB->record_exists('role_assignments',
            ['contextid' => $context2->id, 'userid' => $user1->id, 'roleid' => $teacherrole->id]));
        $this->assertTrue($DB->record_exists('role_assignments',
            ['contextid' => $context2->id, 'userid' => $user1->id, 'roleid' => $studentrole->id]));

        $sslcommerzplugin->set_config('expiredaction', ENROL_EXT_REMOVED_UNENROL);
        role_assign($studentrole->id, $user3->id, $context1->id);
        role_assign($studentrole->id, $user2->id, $context2->id);
        role_assign($teacherrole->id, $user1->id, $context2->id);
        $this->assertEquals(9, $DB->count_records('user_enrolments'));
        $this->assertEquals(9, $DB->count_records('role_assignments'));
        $this->assertEquals(6, $DB->count_records('role_assignments', ['roleid' => $studentrole->id]));
        $this->assertEquals(2, $DB->count_records('role_assignments', ['roleid' => $teacherrole->id]));
        $sslcommerzplugin->sync($trace);
        $this->assertEquals(6, $DB->count_records('user_enrolments'));
        $this->assertFalse($DB->record_exists('user_enrolments', ['enrolid' => $instance1->id, 'userid' => $user3->id]));
        $this->assertFalse($DB->record_exists('user_enrolments', ['enrolid' => $instance2->id, 'userid' => $user2->id]));
        $this->assertFalse($DB->record_exists('user_enrolments', ['enrolid' => $instance3->id, 'userid' => $user1->id]));
        $this->assertEquals(5, $DB->count_records('role_assignments'));
        $this->assertEquals(4, $DB->count_records('role_assignments', ['roleid' => $studentrole->id]));
        $this->assertEquals(1, $DB->count_records('role_assignments', ['roleid' => $teacherrole->id]));
    }

    /**
     * Test for getting user enrolment actions.
     */
    public function test_get_user_enrolment_actions()
    {
        global $CFG, $PAGE;
        $this->resetAfterTest();

        // Set page URL to prevent debugging messages.
        $PAGE->set_url('/enrol/editinstance.php');

        $pluginname = 'sslcommerz';

        // Only enable the sslcommerz enrol plugin.
        $CFG->enrol_plugins_enabled = $pluginname;

        $generator = $this->getDataGenerator();

        // Get the enrol plugin.
        $plugin = enrol_get_plugin($pluginname);

        // Create a course.
        $course = $generator->create_course();
        // Enable this enrol plugin for the course.
        $plugin->add_instance($course);

        // Create a student.
        $student = $generator->create_user();
        // Enrol the student to the course.
        $generator->enrol_user($student->id, $course->id, 'student', $pluginname);

        require_once $CFG->dirroot.'/enrol/locallib.php';
        $manager = new course_enrolment_manager($PAGE, $course);
        $userenrolments = $manager->get_user_enrolments($student->id);
        $this->assertCount(1, $userenrolments);

        $ue = reset($userenrolments);

        // Login as admin to see all enrol actions.
        $this->setAdminUser();
        $actions = $plugin->get_user_enrolment_actions($manager, $ue);

        // sslcommerz enrolment has 2 enrol actions for active users when logged in as admin: edit and unenrol.
        $this->assertCount(2, $actions);

        // Enrol actions when viewing as a teacher.
        // Create a teacher.
        $teacher = $generator->create_user();
        // Enrol the teacher to the course.
        $generator->enrol_user($teacher->id, $course->id, 'editingteacher', $pluginname);
        // Login as the teacher.
        $this->setUser($teacher);
        $actions = $plugin->get_user_enrolment_actions($manager, $ue);
        // Teachers don't have the enrol/sslcommerz:unenrol capability by default, but have enrol/sslcommerz:manage.
        $this->assertCount(1, $actions);
    }

    /**
     * Disable Plugin.
     *
     * @return void
     */
    protected function disable_plugin()
    {
        $enabled = enrol_get_plugins(true);
        unset($enabled['sslcommerz']);
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }
}
