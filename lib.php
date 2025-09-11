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
 * enrol_wsscol_plugin class
 *
 * @package enrol_wsscol
 * @author Serge FELIX<serge.felix@univ-lyon2.fr>
 * @copyright Université Lumière Lyon 2  {@link http://www.univ-lyon2.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\task\manager;

class enrol_wsscol_plugin extends enrol_plugin {
    public const IDENTIFICATION = 'wsscol';
    public const INSTANCE_ETAT_AUTO = 1;
    public const INSTANCE_ETAT_MANUAL = 0;
    public const WS_TABLE_NAME = 'enrol_wsscol_scolapps';

    /**
     * sync_enrolments : sync students of all wsscol instances
     *
     * @param progress_trace $trace
     * Return
     */
    public function sync_enrolments(progress_trace $trace = null) {
        global $DB;
        if (is_null($trace)) {
            $trace = new text_progress_trace();
        }
        $trace->output('Starting user enrolment synchronisation...');
        //$DB->set_debug(TRUE);
        $sql = "SELECT e.*
                      FROM {enrol} e
                      LEFT JOIN {enrol_wsscol_scolapps} ewa ON e.customint2 = ewa.id
                     WHERE ewa.status = 1";
        $instances = $DB->get_recordset_sql($sql);
        //$instances = $DB->get_records('enrol', array('enrol' => 'wsscol'));
        foreach ($instances as $instance) {
            $trace->output('====sync : ' . $instance->customchar1.' courseid : '.$instance->courseid);
            $this->sync_enrolment($instance, $trace);
        }
    }

    /**
     * sync_enrolment : sync students of one wsscol instance
     *
     * @param $instance stdClass : wsscol instance object
     * @param progress_trace $trace
     * Return void
     */
    public function sync_enrolment(stdClass $instance, progress_trace $trace) {
        global $CFG, $DB;
        // ADE is Planning App in our university
        // Get current list of enrolled users with their roles.
        $currentroles = array();
        if (!$context = context_course::instance($instance->courseid, IGNORE_MISSING)) {
            // Weird.
            return;
        }
        $sql = "SELECT u.id AS userid, u.username, ue.status, ra.roleid
                      FROM {user} u
                      JOIN {role_assignments} ra ON (ra.userid = u.id AND ra.component = 'enrol_wsscol' AND ra.itemid = :enrolid)
                 LEFT JOIN {user_enrolments} ue ON (ue.userid = u.id AND ue.enrolid = ra.itemid)
                     WHERE u.deleted = 0";
        $params = array('enrolid' => $instance->id);
        $usersws = array();
	if (isset($instance->customint2)) {
            $usersws = \enrol_wsscol\schoolapp::getinstance($instance->customint2)->getstudents($instance->customchar1);
            if (empty($usersws)) {
                $trace->output($instance->id . ' : empty group webservice side, do nothing, it is weird',1);
                return;
            }
        } else {
            $trace->output($instance->id . ' : no customid',1);
            return;
        }

        // We get enrol's list via enrol instance.
        $rs = $DB->get_recordset_sql($sql, $params);

        foreach ($rs as $ue) {
            // The Student is well enrolled in ADE.
            if (array_key_exists($ue->userid, $usersws)) {
                unset ($usersws[$ue->userid]);
                // He is enrolled in course but suspended => we unsuspended him.
                if ($ue->status == ENROL_USER_SUSPENDED) {
                    $trace->output($ue->username . ' : activate', 2);
                    $this->update_user_enrol($instance, $ue->userid, ENROL_USER_ACTIVE);
                }
                // He is enrolled but not with good role.
                if ($ue->roleid != $instance->roleid) {
                    $trace->output($ue->username . ' : Re-assigning role ' . $instance->roleid, 2);
                    // TODO, Do we unassign a role that it can be assigned manually ?
                    // TODO role_unassign($ue->roleid, $ue->userid, $context->id, 'enrol_wsscol', $instance->id).
                    role_assign($instance->roleid, $ue->userid, $context->id, 'enrol_wsscol', $instance->id);
                }
                // Now unenrolment (suspended).
            } else if ($ue->status != ENROL_USER_SUSPENDED) {
                $this->update_user_enrol($instance, $ue->userid, ENROL_USER_SUSPENDED);
                $trace->output($ue->username . ' : suspended', 2);
            }
        }
        // Enrolments.
        if ($usersws) {
            foreach ($usersws as $userws) {
                $trace->output($userws->username . ' : inscrit', 2);
                $this->enrol_user($instance, $userws->id, $instance->roleid);
            }
        }
        $this->sync_group($instance, $trace);
        $trace->finished();
        $rs->close();
    }

    /**
     * sync_group : sync students of a group from it wsscol instance
     *
     * @param stdClass $instance
     * @param progress_trace $trace
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function sync_group(stdClass $instance, progress_trace $trace) {
        global $DB;
        // First we check if the related group moodle exist.
        // If not we re-create it.
        $groupid = $this->add_group_moodle($instance);
        if (!$context = context_course::instance($instance->courseid, IGNORE_MISSING)) {
            // Weird.
            return;
        }
        $trace->output('sync group '.$groupid,1);
        // Get the actives members of the enrolment method.
        $sql = "SELECT u.id, u.username, ue.status
                      FROM {user} u
                      JOIN {user_enrolments} ue ON (ue.userid = u.id)
                      WHERE u.deleted = 0 AND ue.enrolid = :enrolid AND ue.status = :status";
        $params = array('enrolid' => $instance->id,'status' => ENROL_USER_ACTIVE);
        // We get enrol's list via enrol instance.
        $enrolmembers = $DB->get_recordset_sql($sql, $params);
        // Get the members of group.
        $groupmembers = groups_get_members($groupid, 'u.username, u.id');
        // Loop on users enrolled to sync.
        // At end, groupmembers will contain all members to remove.
        foreach ($enrolmembers as $ue) {
            // The users are already in group.
            if (array_key_exists($ue->username, $groupmembers)) {
                unset ($groupmembers[$ue->username]);
                // The users is not in group yet.
            } else {
                groups_add_member($groupid, $ue->id);
                $trace->output('add '.$ue->username.' in '.$groupid,2);
            }
        }
        // Now we remove all users stay in $groupmembers that have the role of the instance.
        foreach ($groupmembers as $groupmember) {
            $roles_member = get_user_roles($context,$groupmember->id,false);
            foreach ($roles_member as $role_member) {
                if ($role_member->roleid == $instance->roleid) {
                    groups_remove_member($groupid, $groupmember->id);
                    $trace->output('remove '.$groupmember->id.' in '.$groupid,2);
                    break;
                }
            }
        }
        $enrolmembers->close();
    }

    /**
     * Test if a group for wsscol instance exist.
     * If not, add a new one.
     *
     * @param stdClass $instance
     * @return int id of new group
     */
    public function add_group_moodle($instance) {
        global $CFG;
        require_once($CFG->dirroot . '/group/lib.php');
        $data = new stdClass();
        $data->courseid = $instance->courseid;
        var_dump($data);
        $data->idnumber = $this->code_group_idnumber($instance);
        $group = groups_get_group_by_idnumber($data->courseid, $data->idnumber);
        if (!$group) {
            $data->name = $instance->customchar1;
            $data->description = 'groupe automatique';
            $data->descriptionformat = FORMAT_HTML;
            $groupid = groups_create_group($data);
        } else {
            $groupid = $group->id;
        }
        if (is_number($groupid)) {
            return $groupid;
        } else {
            return false;
        }
    }

    /**
     * Update the name of all groups
     * Use as a one shot modification with a cli
     *
     * @param progress_trace $trace
     * @return void
     */
    public function bulk_update_groups(progress_trace $trace, $test = true, $delete = false) {
        global $CFG;
        global $DB;
        require_once($CFG->dirroot . '/group/lib.php');
        $message_test = ($test)?'Start in Test Mode, No Action':'Start in Action Mode';
        $params = array('idnumber' => 'sfx%', 'description' => 'groupe automatique', 'name' => '%doublon%');
        $sql = "SELECT g.id,g.courseid,g.idnumber 
                      FROM {groups} g
                      WHERE g.idnumber LIKE :idnumber and description = :description and name LIKE :name";
        $rs = $DB->get_recordset_sql($sql,$params);
        $trace->output($message_test);
        foreach ($rs as $group) {
            $code = $this->decode_group_idnumber($group->idnumber);
            $instance = $DB->get_record('enrol', array('id' => $code->instanceid,'courseid'=> $group->courseid));
            $trace->output('=== '.$group->id.' '.$group->idnumber);
            // Exclude some courses.
            $courses_exclude = array();
            if (in_array($group->courseid,$courses_exclude)) {
                //$data->name = $instance->customchar1;
                continue;
            }
            // If method enrol don't exist, it's a ghost group, delete it.
            if (!$instance) {
                $trace->output('issue ghost group groupid : ' . $group->id . ', idnumber : ' . $group->idnumber . ', enrolid :' .
                            $code->instanceid . ', courseid : ' . $group->courseid,2);
                if ($delete) {
                    groups_delete_group($group->id);
                }
                continue;
            }
            // Check what we want to check.
            // Here if the group is use in availability
            $params = array('groupid' => '%{"type":"group","id":'.$group->id.'}%');
            $sql = "SELECT cm.id,cm.course
                      FROM {course_modules} cm
                      WHERE cm.availability LIKE :groupid";
            $cm = $DB->get_records_sql($sql,$params);
            if (!$cm) {
                $trace->output('delete it cause not use',2);
                if ($delete) {
                    groups_delete_group($group->id);
                }               
            } else {
                $trace->output('keep it cause use',2);
            }
        }
        $rs->close();
    }

    public function code_group_idnumber(stdClass $instance) {
        return $this->get_name() . '/' . $instance->id;
    }

    public function decode_group_idnumber(string $idnumber) {
        $instanceid=substr(strrchr($idnumber,'/'),1);
        $name = strstr($idnumber,'/',true);
        $code = new stdClass();
        $code->name = $name;
        $code->instanceid = $instanceid;
        return $code;
    }

    /**
     * Returns fields for new instances.
     *
     * @param string search
     * @param string $username
     * @param int $wsid
     * @param bool $type (manual ou auto constant)
     * @return array
     */
    public function get_instance_fields(string $search, string $username, int $wsid, bool $type) {
        $fields = $this->get_instance_defaults();
        $fields['customchar1'] = $search;
        $fields['customchar3'] = $username;
        $fields['customint2'] = $wsid;
        $fields['customint3'] = $type;
        return $fields;
    }

    /**
     * Returns defaults for new instances.
     *
     * @param string search
     * @return array
     */
    public function get_instance_defaults() {
        $fields = array();
        $fields['roleid'] = $this->get_config('roleid');
        return $fields;
    }

    /**
     * Add new instance of enrol plugin.
     *
     * @param object $course
     * @param array $fields instance fields
     * @return int id of new instance, null if can not be created
     */
    public function add_instance($course, array $fields = null) {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . '/group/lib.php');
        require_once('classes/task/sync_enrolment.php');
        $wsapp = $DB->get_record(self::WS_TABLE_NAME, array('id' => $fields['customint2']));
        $fields['name'] =
            $wsapp->name . ' (' . self::get_name_instance_etat($fields['customint3']) . '): ' . $fields['customchar1'];
        $fields['roleid'] = $wsapp->role;
        $instanceid = parent::add_instance($course, $fields);
        // It's instance id that is re-sended.
        if (is_int($instanceid)) {
            $instance = $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);
            if ($instance) {
                $syncenrolmenttask = new \enrol_wsscol\task\sync_enrolment();
                $syncenrolmenttask->set_custom_data(array('instance' => $instance));
                $syncenrolmenttask->set_userid($USER->id);
                manager::queue_adhoc_task($syncenrolmenttask);
            }
            //$groupid = $this->add_group_moodle($instance);
        }
        return $instanceid;
    }

    /**
     * get_name_instance_etat retrun state
     *
     * @param int $etat state number
     * @return string name of the state
     */
    public static function get_name_instance_etat(int $etat) {
        switch ($etat) {
            case self::INSTANCE_ETAT_MANUAL:
                return 'manuel';
                break;
            case self::INSTANCE_ETAT_AUTO:
                return 'auto';
                break;
        }
    }

    /**
     * {@inheritDoc}
     * @see enrol_plugin::delete_instance()
     */
    public function delete_instance($instance) {
        $groupidnumber = $this->code_group_idnumber($instance);
        $groupobject = groups_get_group_by_idnumber($instance->courseid, $groupidnumber);
        groups_delete_group($groupobject);
        $return = parent::delete_instance($instance);
        return $return;
    }

    /**
     * {@inheritDoc}
     * @see enrol_plugin::update_instance()
     */
    public function update_instance($instance, $data) {
        global $USER;
        require_once('classes/task/sync_enrolment.php');
        $return = parent::update_instance($instance, $data);
        if ($return) {
            $syncenrolmenttask = new \enrol_wsscol\task\sync_enrolment();
            $syncenrolmenttask->set_custom_data(array('instance' => $instance));
            $syncenrolmenttask->set_userid($USER->id);
            manager::queue_adhoc_task($syncenrolmenttask);
        }
        return $return;
    }

    /**
     * {@inheritDoc}
     * @see enrol_plugin::can_delete_instance()
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/wsscol:config', $context);
    }

    /**
     * {@inheritDoc}
     * @see enrol_plugin::can_hide_show_instance()
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);

        if (!has_capability('enrol/wsscol:config', $context)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     * @see enrol_plugin::edit_instance_form()
     */
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {
        global $CFG, $DB, $USER;
        $role = $this->extend_assignable_roles($context, $instance->roleid);
        $selectroles = $mform->addElement('select', 'roleid', get_string('role', 'enrol_wsscol'), $role);
        $selectroles->setSelected($instance->roleid);
        $options = array('size' => '20', 'maxlength' => '20');
        $wsappsselect = $this->get_menu_active_scolapps();
        $mform->addElement('select', 'customint2', get_string('customint2', 'enrol_wsscol'), $wsappsselect);
        $mform->addElement('text', 'customchar1', get_string('searchlabel', 'enrol_wsscol'), $options);

        $mform->setType('customchar1', PARAM_TEXT);
        $mform->setType('customint2', PARAM_INT);
        $mform->setType('customint3', PARAM_INT);
        $mform->setType('customchar3', PARAM_TEXT);
        // If instance is created.
        if (is_null($instance->id)) {
            $mform->addElement('hidden', 'customint3', self::INSTANCE_ETAT_MANUAL);
            $mform->addElement('hidden', 'customchar3', $USER->username);
            // If it is modified.
        } else {
            $mform->freeze('customchar1');
            $mform->addElement('text', 'customint3', get_string('type_instance_label', 'enrol_wsscol'));
            $mform->addElement('text', 'customchar3', get_string('creator_instance_label', 'enrol_wsscol'));
            $mform->freeze('customint3');
            $mform->freeze('customchar3');
        }

        if (enrol_accessing_via_instance($instance)) {
            $warntext = get_string('instanceeditwsscolwarningtext', 'core_enrol');
            $mform->addElement('static', 'wsscolwarn', get_string('instanceeditwsscolwarning', 'core_enrol'), $warntext);
        }
    }

    /**
     * @param $context
     * @param string $defaultrole
     * @return array roles
     */
    public function extend_assignable_roles($context, $defaultrole) {
        global $DB;
        $roles = get_assignable_roles($context, ROLENAME_BOTH);
        if (!isset($roles[$defaultrole])) {
            if ($role = $DB->get_record('role', array('id' => $defaultrole))) {
                $roles[$defaultrole] = role_get_name($role, $context, ROLENAME_BOTH);
            }
        }
        return $roles;
    }

    /**
     * {@inheritDoc}
     * @see enrol_plugin::use_standard_editing_ui()
     */
    public function use_standard_editing_ui() {
        return true;
    }

    /**
     * {@inheritDoc}
     * @see enrol_plugin::edit_instance_validation()
     */
    public function edit_instance_validation($data, $files, $instance, $context) {
        $errors = array();
        $validstatus = array_keys($this->get_status_options());
        $context = context_course::instance($instance->courseid);
        $validroles = array_keys($this->extend_assignable_roles($context, $instance->roleid));
        if (isset($data['customint2'])) {
            $data['customint2'] = intval($data['customint2']);
        } else {
            $errors['customint2'] = get_string('error_customint2', 'enrol_wsscol');
        }
        $resp = \enrol_wsscol\schoolapp::getinstance($data['customint2'])->getstudents($data['customchar1']);
        if (empty($resp)) {
            $errors['customchar1'] = 'mauvais codeX ou groupe vide';
        }
        $tovalidate = array(
                'roleid' => $validroles,
                'customchar1' => PARAM_TEXT
        );
        $typeerrors = $this->validate_param_types($data, $tovalidate);
        $errors = array_merge($errors, $typeerrors);

        return $errors;
    }

    /**
     * Return an array of valid options for the status.
     *
     * @return array
     */
    protected function get_status_options() {
        $options = array(ENROL_INSTANCE_ENABLED => get_string('yes'),
                ENROL_INSTANCE_DISABLED => get_string('no'));
        return $options;
    }

    /**
     * {@inheritDoc}
     * Add our bulk enrolment button
     *
     * @see enrol_plugin::get_manual_enrol_button()
     */
    public function get_manual_enrol_button(course_enrolment_manager $manager) {
        global $USER;
        $url = new moodle_url('/enrol/wsscol/add_instances.php');
        $courseid = $manager->get_course()->id;
        $context = context_course::instance($courseid, MUST_EXIST);
        if ($USER || has_capability('enrol/wsscol:config', $context)) {
            $url->param('courseid', $courseid);
            $button = new enrol_user_button($url, get_string('bulk_enrolment_button', 'enrol_wsscol'), 'get');
            $button->formid = 'bulk_wsscol_enrolment';
            $button->class = 'singlebutton';
            return $button;
        } else {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     * @see enrol_plugin::can_add_instance()
     */
    public function can_add_instance($courseid) {
        $context = context_course::instance($courseid, MUST_EXIST);
        if (!has_capability('moodle/course:enrolconfig', $context)) {
            return false;
        }
        return true;
    }

    /**
     * Add new instance of enrol plugin with default settings.
     *
     * @param stdClass $course
     * @return int id of new instance
     */
    public function add_default_instance($course) {
        // Don't need default, The add of an instance ask for a code entry.
    }

    public function roles_protected() {
        return true;
    }

    public function allow_unenrol(stdClass $instance) {
        return false;
    }

    public function allow_manage(stdClass $instance) {
        return false;
    }

    public function show_enrolme_link(stdClass $instance) {
        return false;
    }
    public function get_menu_active_scolapps() {
        global $DB;
        // We do'nt select scolapps that are not activated.
        $select = 'status = 1 AND ';
        $select .= $DB->sql_isnotempty(enrol_wsscol_plugin::WS_TABLE_NAME, 'searchgroups_uri', true, false);
        $wsscolappoptions = $DB->get_records_select_menu(enrol_wsscol_plugin::WS_TABLE_NAME, $select, null, null, 'id,name');
        return $wsscolappoptions;
    }
}

