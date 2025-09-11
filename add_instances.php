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
 * Copyright (c) 2023.
 * page to add multiple wsscol enrolment instances
 *
 * @package enrol_wsscol
 * @author Serge FELIX <serge.felix@univ-lyon2.fr>
 * @copyright Université Lumière Lyon 2  {@link http://www.univ-lyon2.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once('lib.php');
require_once('classes/schoolapp.php');
require_once("$CFG->libdir/formslib.php");
require_once("$CFG->dirroot/course/externallib.php");
require("$CFG->libdir/tablelib.php");

/***** The Form *****/
class bulk_enrolments_form extends moodleform {
    public function definition() {
        global $CFG;
        global $USER;

        $datas = $this->_customdata;
        $schoolinggroups = \enrol_wsscol\schoolapp::getinstance($datas['wsid'])->search($datas['search']);
        $mform = $this->_form;
        $this->add_action_buttons();
        if ($schoolinggroups) {
            $iterator = new ArrayIterator($schoolinggroups);
            $iterator->asort();
            // We create an array of wsscol's enrol instance to not propose instance already added.
            $instancesenrolment = enrol_get_instances($datas['courseid'], false);
            $wsscolinstances = array();
            foreach ($instancesenrolment as $instanceenrolment) {
                if ($instanceenrolment->enrol == 'wsscol' && $instanceenrolment->customint2 == $datas['wsid']) {
                    $wsscolinstances[$instanceenrolment->customchar1] = $instanceenrolment;
                }
            }
            while ($iterator->valid()) {
                if ($iterator->current()) {
                    if (key_exists($iterator->current()->code, $wsscolinstances)) {
                        unset($wsscolinstances[$iterator->current()->code]);
                    } else {
                        $mform->addElement(
                                'checkbox',
                                'groups[' . $iterator->current()->code . ']', // Name.
                                $iterator->current()->code . " : " . $iterator->current()->name,  // Left label.
                                '',
                                []
                        );
                    }
                }
                $iterator->next();
            }
            $this->add_action_buttons();
        } else {
            $mform->addElement('html', '<div class="alert">' . get_string('zeroresult', 'enrol_wsscol') . '.</div>');
        }
    }
}

/***** The Page *****/

global $USER, $DB;

$wsscol = enrol_get_plugin('wsscol');

// Search related params.
$selectparamname = 'wsid';
$inputparamname = 'search';
$courseparamname = 'courseid';

$search = optional_param($inputparamname, '', PARAM_RAW);
$wsid = optional_param($selectparamname, 0, PARAM_INT);
$courseid = required_param($courseparamname, PARAM_INT);
$issearching = ($search !== '');
$url = new moodle_url('/enrol/wsscol/add_instances.php');
if ($search !== '') {
    $url->param($inputparamname, $search);
}
if ($wsid !== '') {
    $url->param($selectparamname, $wsid);
}
if ($courseid !== '') {
    $url->param($courseparamname, $courseid);
}

$course = get_course($courseid);
$context = context_course::instance($courseid, MUST_EXIST);
require_login($course);
course_require_view_participants($context);
$test = has_capability('enrol/wsscol:config', $context);
if (!$USER || !has_capability('enrol/wsscol:config', $context)) {
    throw new moodle_exception('nopermissiontoviewpage');
}

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_heading(get_string('add_instances_title', 'enrol_wsscol'));
$PAGE->set_title(get_string('add_instances_title', 'enrol_wsscol'));
$PAGE->set_pagelayout('incourse');

echo $OUTPUT->header();
$wsscolappoptions = $wsscol->get_menu_active_scolapps();
if ($wsscolappoptions) {
    $options = array();
    foreach ($wsscolappoptions as $key => $value) {
        $options[] = array('value' => $key, 'name' => $value, 'selected' => ($key === $wsid) ? 'selected' : '');
    }
    $hiddenfields = array();
    $hiddenfields[] = array('name' => $courseparamname, 'value' => $courseid);
    $datas = [
            'action' => $url,
            'inputname' => $inputparamname,
            'selectname' => $selectparamname,
            'searchstring' => get_string('searchstring', 'enrol_wsscol'),
            'searchplaceholder' => get_string('searchplaceholder', 'enrol_wsscol'),
            'value' => 'search',
            'options' => $options,
            'label-select' => get_string('label', 'enrol_wsscol'),
            'extraclasses' => 'input-group',
            'hiddenfields' => $hiddenfields
    ];
    if ($search) {
        $datas['query'] = $search;
    }
    $out = '';
    $out .= html_writer::start_div('row m-3 justify-content-center');
    $out .= $OUTPUT->render_from_template('enrol_wsscol/search_select', $datas);
    $out .= html_writer::end_div();
    echo $out;
    $searchdatas = array($selectparamname => $wsid, $inputparamname => $search, $courseparamname => $courseid);
    if ($issearching) {
        $mform = new bulk_enrolments_form($url, $searchdatas);
        if ($datas = $mform->get_data()) {
            // In this case you process validated data. $mform->get_data() returns data posted in form.
            foreach ($datas->groups as $group => $value) {
                $datawscol = $wsscol->get_instance_fields(
                        $group,
                        $USER->username,
                        intval($wsid),
                        enrol_wsscol_plugin::INSTANCE_ETAT_MANUAL
                );
                $wsscol->add_instance($course, $datawscol);
            }
            $urlredirect = new moodle_url('/enrol/instances.php');
            $urlredirect->param('id', $courseid);
            redirect($urlredirect);
        } else {
            $mform->display();
        }
    }
} else {
    $content = format_text('aucune application de scolarité permettant d\'effectuer des inscriptions en masse n\'a été paramétrée');
    echo html_writer::div($content);
}
echo $OUTPUT->footer();
