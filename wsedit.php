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
 * Page to edit webservices settings.
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

/**
 * Form definition.
 * @author Serge FELIX <serge.felix@univ-lyon2.fr>
 * @copyright Université Lumière Lyon 2  {@link http://www.univ-lyon2.fr}
 */
class wsedit_form extends moodleform {
    protected $isadding;

    public function __construct($actionurl, $isadding) {
        $this->isadding = $isadding;
        parent::__construct($actionurl);
    }

    public function definition() {
        global $CFG, $USER;

        $mform = $this->_form;

        // SECTION 1 : General settings.
        $mform->addElement('header', 'general', get_string('ws_section_general', 'enrol_wsscol'));
        $mform->setExpanded('general', true);

        $mform->addElement('hidden', 'status', 1);
        $mform->setType('status', PARAM_INT);

        $mform->addElement('text', 'name', get_string('ws_name', 'enrol_wsscol'), ['size' => '32']);
        $mform->setType('name', PARAM_NOTAGS);
        $mform->addRule('name', null, 'required');
        $mform->addRule('name', null, 'maxlength', 16);
        $mform->addHelpButton('name', 'ws_name', 'enrol_wsscol');

        $mform->addElement('select', 'type', get_string('type', 'enrol_wsscol'), [
            ''       => get_string('typegeneric', 'enrol_wsscol'),
            'pegase' => 'PEGASE',
            'aurion' => 'Aurion',
            'apogee' => 'Apogée',
        ]);
        $mform->setType('type', PARAM_NOTAGS);
        $mform->addHelpButton('type', 'type', 'enrol_wsscol');

        $options = get_default_enrol_roles(context_system::instance());
        $mform->addElement('select', 'role', get_string('ws_role', 'enrol_wsscol'), $options);
        $mform->setType('role', PARAM_INT);
        $mform->addRule('role', null, 'required');
        $mform->addHelpButton('role', 'ws_role', 'enrol_wsscol');

        // SECTION 2 : Webservice connection.
        $mform->addElement('header', 'connection', get_string('ws_section_connection', 'enrol_wsscol'));
        $mform->setExpanded('connection', true);

        $mform->addElement('text', 'wshost', get_string('ws_host', 'enrol_wsscol'), ['size' => '64']);
        $mform->setType('wshost', PARAM_NOTAGS);
        $mform->addRule('wshost', null, 'required');
        $mform->addRule('wshost', null, 'maxlength', 253);
        $mform->addHelpButton('wshost', 'ws_host', 'enrol_wsscol');

        $mform->addElement('text', 'baseurl', get_string('ws_baseurl', 'enrol_wsscol'), ['size' => '64']);
        $mform->setType('baseurl', PARAM_NOTAGS);
        $mform->addHelpButton('baseurl', 'ws_baseurl', 'enrol_wsscol');

        $mform->addElement('text', 'wsuser', get_string('ws_user', 'enrol_wsscol'), ['size' => '64']);
        $mform->setType('wsuser', PARAM_NOTAGS);
        $mform->addRule('wsuser', null, 'maxlength', 128);

        $mform->addElement('passwordunmask', 'wspassword', get_string('ws_password', 'enrol_wsscol'), ['size' => '64']);
        $mform->setType('wspassword', PARAM_NOTAGS);

        // SECTION 3 : Token authentication (optional).
        $mform->addElement('header', 'tokenauth', get_string('ws_section_tokenauth', 'enrol_wsscol'));
        $mform->setExpanded('tokenauth', false);

        $mform->addElement(
            'static',
            'tokenauth_desc',
            '',
            html_writer::tag(
                'div',
                get_string('ws_tokenauth_desc', 'enrol_wsscol'),
                ['class' => 'alert alert-info']
            )
        );

        $mform->addElement('select', 'tokenmethod', get_string('ws_tokenmethod', 'enrol_wsscol'), [
            ''    => get_string('ws_tokenmethod_none', 'enrol_wsscol'),
            'cas' => 'CAS',
        ]);
        $mform->setType('tokenmethod', PARAM_NOTAGS);
        $mform->addHelpButton('tokenmethod', 'ws_tokenmethod', 'enrol_wsscol');

        $mform->addElement('text', 'authurl', get_string('ws_authurl', 'enrol_wsscol'), ['size' => '64']);
        $mform->setType('authurl', PARAM_NOTAGS);
        $mform->addHelpButton('authurl', 'ws_authurl', 'enrol_wsscol');

        // SECTION 4 : Students retrieval.
        $mform->addElement('header', 'students', get_string('ws_section_students', 'enrol_wsscol'));
        $mform->setExpanded('students', true);

        $mform->addElement(
            'static',
            'getstudents_uri_desc',
            '',
            html_writer::tag(
                'div',
                get_string('ws_getstudents_uri_desc', 'enrol_wsscol'),
                ['class' => 'alert alert-info']
            )
        );

        $mform->addElement('text', 'getstudents_uri', get_string('ws_getstudents_uri', 'enrol_wsscol'), ['size' => '64']);
        $mform->setType('getstudents_uri', PARAM_NOTAGS);
        $mform->addRule('getstudents_uri', null, 'required');
        $mform->addHelpButton('getstudents_uri', 'ws_getstudents_uri', 'enrol_wsscol');

        $mform->addElement('text', 'getstudents_periode', get_string('ws_getstudents_periode', 'enrol_wsscol'), ['size' => '32']);
        $mform->setType('getstudents_periode', PARAM_NOTAGS);
        $mform->addRule('getstudents_periode', null, 'maxlength', 32);
        $mform->addHelpButton('getstudents_periode', 'ws_getstudents_periode', 'enrol_wsscol');

        $mform->addElement('text', 'getstudents_structure', get_string('ws_getstudents_structure', 'enrol_wsscol'), ['size' => '32']);
        $mform->setType('getstudents_structure', PARAM_NOTAGS);
        $mform->addRule('getstudents_structure', null, 'maxlength', 32);
        $mform->addHelpButton('getstudents_structure', 'ws_getstudents_structure', 'enrol_wsscol');

        // SECTION 5 : Student ID mapping.
        $mform->addElement('header', 'mapping', get_string('ws_section_mapping', 'enrol_wsscol'));
        $mform->setExpanded('mapping', true);

        $mform->addElement(
            'static',
            'mapping_desc',
            '',
            html_writer::tag(
                'div',
                get_string('ws_mapping_desc', 'enrol_wsscol'),
                ['class' => 'alert alert-info']
            )
        );

        $mform->addElement('text', 'getstudent_id_ws', get_string('ws_getstudent_id_ws', 'enrol_wsscol'), ['size' => '32']);
        $mform->setType('getstudent_id_ws', PARAM_NOTAGS);
        $mform->addRule('getstudent_id_ws', null, 'required');
        $mform->addRule('getstudent_id_ws', null, 'maxlength', 16);
        $mform->addHelpButton('getstudent_id_ws', 'ws_getstudent_id_ws', 'enrol_wsscol');

        require_once($CFG->dirroot . '/user/profile/lib.php');
        $idfields = [
            'username'    => new lang_string('username'),
            'firstname'   => new lang_string('firstname'),
            'lastname'    => new lang_string('lastname'),
            'idnumber'    => new lang_string('idnumber'),
            'email'       => new lang_string('email'),
            'phone1'      => new lang_string('phone1'),
            'phone2'      => new lang_string('phone2'),
            'department'  => new lang_string('department'),
            'institution' => new lang_string('institution'),
            'city'        => new lang_string('city'),
            'country'     => new lang_string('country'),
        ];

        $profilefields = profile_get_custom_fields();
        foreach ($profilefields as $field) {
            if ($field->param2 > 255 || $field->datatype != 'text') {
                continue;
            }
            $idfields['profile_field_' . $field->shortname] = $field->name . ' *';
        }

        $mform->addElement('select', 'getstudent_id_local', get_string('ws_getstudent_id_local', 'enrol_wsscol'), $idfields);
        $mform->addHelpButton('getstudent_id_local', 'ws_getstudent_id_local', 'enrol_wsscol');

        // SECTION 6 : Group search (optional).
        $mform->addElement('header', 'groupsearch', get_string('ws_section_groupsearch', 'enrol_wsscol'));
        $mform->setExpanded('groupsearch', false);

        $mform->addElement(
            'static',
            'groupsearch_desc',
            '',
            html_writer::tag(
                'div',
                get_string('ws_groupsearch_desc', 'enrol_wsscol'),
                ['class' => 'alert alert-info']
            )
        );

        $mform->addElement('text', 'searchgroups_uri', get_string('ws_searchgroups_uri', 'enrol_wsscol'), ['size' => '64']);
        $mform->setType('searchgroups_uri', PARAM_NOTAGS);

        $mform->addElement('text', 'searchgroups_code_ws', get_string('ws_searchgroups_code_ws', 'enrol_wsscol'), ['size' => '32']);
        $mform->setType('searchgroups_code_ws', PARAM_NOTAGS);
        $mform->addRule('searchgroups_code_ws', null, 'maxlength', 16);

        $mform->addElement('text', 'searchgroups_name_ws', get_string('ws_searchgroups_name_ws', 'enrol_wsscol'), ['size' => '32']);
        $mform->setType('searchgroups_name_ws', PARAM_NOTAGS);
        $mform->addRule('searchgroups_name_ws', null, 'maxlength', 16);

        $submitlabel = null;
        if ($this->isadding) {
            $submitlabel = get_string('wsedit_submitlabel', 'enrol_wsscol');
        }
        $this->add_action_buttons(true, $submitlabel);
    }

    public function validation($data, $files) {
        return [];
    }
}

// PAGE Handling.
require_login();
$context = context_system::instance();
if (!has_capability('moodle/site:config', $context)) {
    throw new moodle_exception('nopermissiontoviewpage');
}

$managewsurl = new moodle_url('/enrol/wsscol/wsmanage.php');
$pluginurl   = new moodle_url('/admin/settings.php', ['section' => 'enrolsettingswsscol']);
$wsid        = optional_param('wsid', 0, PARAM_INT);

$PAGE->set_context($context);
$PAGE->set_url('/enrol/wsscol/wsedit.php', ['wsid' => $wsid]);
$PAGE->set_pagelayout('admin');

if ($wsid) {
    $isadding = false;
    $wsrecord = $DB->get_record(enrol_wsscol_plugin::WS_TABLE_NAME, ['id' => $wsid], '*', MUST_EXIST);
} else {
    $isadding = true;
    $wsrecord = new stdClass();
}

$mform = new wsedit_form($PAGE->url, $isadding);
$mform->set_data($wsrecord);

if ($mform->is_cancelled()) {
    redirect($managewsurl);
} else if ($data = $mform->get_data()) {
    if ($isadding) {
        $DB->insert_record(enrol_wsscol_plugin::WS_TABLE_NAME, $data);
    } else {
        $data->id = $wsid;
        $DB->update_record(enrol_wsscol_plugin::WS_TABLE_NAME, $data);
    }
    redirect($managewsurl);
} else {
    $title = $isadding
        ? get_string('wsedit_title_add', 'enrol_wsscol')
        : get_string('wsedit_title_edit', 'enrol_wsscol');

    $PAGE->set_title($title);
    $PAGE->set_heading($title);

    $PAGE->navbar->add(get_string('administrationsite'), new moodle_url('/' . $CFG->admin));
    $PAGE->navbar->add(get_string('pluginname', 'enrol_wsscol'), $pluginurl);
    $PAGE->navbar->add(get_string('wsmanage_title', 'enrol_wsscol'), $managewsurl);
    $PAGE->navbar->add($title);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($title, 2);
    $mform->display();
    echo $OUTPUT->footer();
}
