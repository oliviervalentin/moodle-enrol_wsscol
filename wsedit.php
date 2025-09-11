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
 * page to edit webservices settings
 *
 * @package enrol_wsscol
 * @author Serge FELIX <serge.felix@univ-lyon2.fr>
 * @copyright Université Lumière Lyon 2  {@link http://www.univ-lyon2.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once('lib.php');
require_once("$CFG->libdir/formslib.php");
require_once("$CFG->dirroot/course/externallib.php");

class wsedit_form extends moodleform {
    protected $isadding;

    public function __construct($actionurl, $isadding) {
        $this->isadding = $isadding;
        parent::__construct($actionurl);
    }

    public function definition() {
        global $CFG;
        global $USER;
        $mform = $this->_form;
        $mform->addElement('hidden', 'status', 1);
        $mform->setType('status', PARAM_INT);
        $mform->addElement('text', 'name', 'name', array('size' => '16'));// Addstring.
        $mform->setType('name', PARAM_NOTAGS);
        $mform->addRule('name', null, 'required');
        $mform->addRule('name', null, 'maxlength', 16);

        $options = get_default_enrol_roles(context_system::instance());
        $defaultrole = get_config('enrol_wsscol', 'roleid');
        $mform->addElement('select', 'role', 'role', $options);
        $mform->setType('role', PARAM_INT);
        $mform->addRule('role', null, 'required');
        $mform->getElement('role')->setSelected($student->id);

        $mform->addElement('text', 'wshost', 'wshost', array('size' => '253'));// Addstring.
        $mform->setType('wshost', PARAM_NOTAGS);
        $mform->addRule('wshost', null, 'required');
        $mform->addRule('wshost', null, 'maxlength', 253);

        $mform->addElement('text', 'wsuser', 'wsuser', array('size' => '64'));// Addstring.
        $mform->setType('wsuser', PARAM_NOTAGS);
        $mform->addRule('wsuser', null, 'maxlength', 128);

        $mform->addElement('passwordunmask', 'wspassword', 'wspassword', array('size' => '64'));// Addstring.
        $mform->setType('wspassword', PARAM_NOTAGS);

        $mform->addElement('text', 'baseurl', 'baseurl', array('size' => '16'));// Addstring.
        $mform->setType('baseurl', PARAM_NOTAGS);

        $mform->addElement('text', 'getstudents_uri', 'getstudents_uri', array('size' => '64'));
        $mform->setType('getstudents_uri', PARAM_NOTAGS);
        $mform->addRule('getstudents_uri', null, 'required');

        $mform->addElement('text', 'getstudent_id_ws', 'getstudent_id_ws', array('size' => '16'));
        $mform->setType('getstudent_id_ws', PARAM_NOTAGS);
        $mform->addRule('getstudent_id_ws', null, 'required');
        $mform->addRule('getstudent_id_ws', null, 'maxlength', 16);

        require_once($CFG->dirroot . '/user/profile/lib.php');
        // Basic fields available in user table.
        $idfields = [
                'username' => new lang_string('username'),
                'firstname' => new lang_string('firstname'),
                'lastname' => new lang_string('lastname'),
                'idnumber' => new lang_string('idnumber'),
                'email' => new lang_string('email'),
                'phone1' => new lang_string('phone1'),
                'phone2' => new lang_string('phone2'),
                'department' => new lang_string('department'),
                'institution' => new lang_string('institution'),
                'city' => new lang_string('city'),
                'country' => new lang_string('country'),
        ];

        // Custom profile fields.
        $profilefields = profile_get_custom_fields();
        foreach ($profilefields as $field) {
            // Only reasonable-length text fields can be used as identity fields.
            if ($field->param2 > 255 || $field->datatype != 'text') {
                continue;
            }
            $idfields['profile_field_' . $field->shortname] = $field->name . ' *';
        }

        $mform->addElement('select', 'getstudent_id_local', 'getstudent_id_local', $idfields);

        $mform->addElement('text', 'searchgroups_uri', 'searchgroups_uri', array('size' => '64'));
        $mform->setType('searchgroups_uri', PARAM_NOTAGS);

        $mform->addElement('text', 'searchgroups_code_ws', 'searchgroups_code_ws', array('size' => '16'));
        $mform->setType('searchgroups_code_ws', PARAM_NOTAGS);
        $mform->addRule('searchgroups_code_ws', null, 'maxlength', 16);

        $mform->addElement('text', 'searchgroups_name_ws', 'searchgroups_name_ws', array('size' => '16'));
        $mform->setType('searchgroups_name_ws', PARAM_NOTAGS);
        $mform->addRule('searchgroups_name_ws', null, 'maxlength', 16);

        $submitlabel = null; // Default.
        if ($this->isadding) {
            $submitlabel = get_string('wsedit_submitlabel', 'enrol_wsscol');
        }
        $this->add_action_buttons(true, $submitlabel);
    }

    // Custom validation should be added here.
    public function validation($data, $files) {
        return array();
    }
}

// PAGE Handling.

require_login();
$context = context_system::instance();
if (!has_capability('moodle/site:config', $context)) {
    throw new moodle_exception('nopermissiontoviewpage');
}
$managewsurl = new moodle_url('/enrol/wsscol/wsmanage.php');
$pluginurl = new moodle_url('/admin/settings.php', array('section' => 'enrolsettingswsscol'));
$wsid = optional_param('wsid', 0, PARAM_INT);
$urlparams = array('wsid' => $wsid);
$PAGE->set_context($context);
$PAGE->set_url('/enrol/wsscol/wsedit.php', $urlparams);
$PAGE->set_pagelayout('admin');

if ($wsid) {
    $isadding = false;
    $wsrecord = $DB->get_record(enrol_wsscol_plugin::WS_TABLE_NAME, array('id' => $wsid), '*', MUST_EXIST);
} else {
    $isadding = true;
    $wsrecord = new stdClass;
}

// Instantiate.
$mform = new wsedit_form($PAGE->url, $isadding);
$mform->set_data($wsrecord);
// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present on form.
    redirect($managewsurl);
} else if ($data = $mform->get_data()) {
    // In this case you process validated data. $mform->get_data() returns data posted in form.
    if ($isadding) {
        $DB->insert_record(enrol_wsscol_plugin::WS_TABLE_NAME, $data);
    } else {
        $data->id = $wsid;
        $DB->update_record(enrol_wsscol_plugin::WS_TABLE_NAME, $data);
    }

    redirect($managewsurl);
} else {
    $PAGE->set_title('add wsbservices');
    $PAGE->set_heading('add wsbservices');

    $PAGE->navbar->add(get_string('administrationsite'), new moodle_url('/' . $CFG->admin));
    $PAGE->navbar->add(get_string('pluginname', 'enrol_wsscol'), $pluginurl);
    $PAGE->navbar->add(get_string('wsmanage_title', 'enrol_wsscol'), $managewsurl);
    $PAGE->navbar->add(get_string('wsedit_title', 'enrol_wsscol'));

    echo $OUTPUT->header();
    echo $OUTPUT->heading('add wsbservices', 2);

    $mform->display();

    echo $OUTPUT->footer();
}
