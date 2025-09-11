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
 * wsscol enrolment plugin settings and presets.
 *
 * @package enrol_wsscol
 * @author Serge FELIX<serge.felix@univ-lyon2.fr>
 * @copyright Université Lumière Lyon 2  {@link http://www.univ-lyon2.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $DB;

if ($ADMIN->fulltree) {

    // Enrol instance defaults.
    $settings->add(new admin_setting_heading('enrol_wsscol_defaults',
            get_string('enrolinstancedefaults', 'admin'),
            get_string('enrolinstancedefaults_desc', 'admin')));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_wsscol/roleid',
                get_string('defaultrole', 'enrol_wsscol'),
                get_string('defaultrole_desc', 'enrol_wsscol'),
                $student->id ?? null,
                $options));
    }

    // Webservice params.
    $settings->add(new admin_setting_heading('enrol_wsscol_webservices',
            get_string('webservices_title', 'enrol_wsscol'), get_string('webservices_desc', 'enrol_wsscol')));

    $link = '<a href="' . $CFG->wwwroot . '/enrol/wsscol/wsmanage.php">' . get_string('wsmanage_link', 'enrol_wsscol') . '</a>';
    $settings->add(new admin_setting_heading('enrol_wsscol_addheading', '', $link));
}
