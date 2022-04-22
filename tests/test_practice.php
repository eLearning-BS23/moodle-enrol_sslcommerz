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
 * Privacy provider tests.
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
class enrol_test_practice extends advanced_testcase
{
    public function test_basics()
    {
        $this->assertFalse(enrol_is_enabled('sslcommerz'));
        $plugin = enrol_get_plugin('sslcommerz');
        $this->assertInstanceOf('enrol_sslcommerz_plugin', $plugin);
        $this->assertEquals(ENROL_EXT_REMOVED_SUSPENDNOROLES, get_config('enrol_sslcommerz', 'expiredaction'));
    }
}
