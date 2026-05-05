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
 * This file keeps track of upgrades to the wsscol enrolment plugin
 *
 * @package enrol_wsscol
 * @author Serge FELIX <serge.felix@univ-lyon2.fr>
 * @copyright Université Lumière Lyon 2  {@link http://www.univ-lyon2.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_enrol_wsscol_upgrade($oldversion) {
    global $CFG, $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2021100100) {
        // Define table enrol_wsscol_scolapps to be created.

        // Define table enrol_wsscol_scolapps to be created.
        $table = new xmldb_table('enrol_wsscol_scolapps');

        // Adding fields to table enrol_wsscol_scolapps.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '16', null, XMLDB_NOTNULL, null, null);
        $table->add_field('wshost', XMLDB_TYPE_CHAR, '253', null, XMLDB_NOTNULL, null, null);
        $table->add_field('wsuser', XMLDB_TYPE_CHAR, '128', null, null, null, null);
        $table->add_field('wspassword', XMLDB_TYPE_CHAR, '128', null, null, null, null);
        $table->add_field('baseurl', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('getstudents_uri', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('getstudent_id_ws', XMLDB_TYPE_CHAR, '16', null, XMLDB_NOTNULL, null, null);
        $table->add_field('getstudent_id_local', XMLDB_TYPE_CHAR, '16', null, XMLDB_NOTNULL, null, null);
        $table->add_field('searchgroups_uri', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('searchgroups_code_ws', XMLDB_TYPE_CHAR, '16', null, null, null, null);
        $table->add_field('getcoursesteacher_uri', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table enrol_wsscol_scolapps.
        $table->add_key('id', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for enrol_wsscol_scolapps.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $sql = "UPDATE {enrol} SET customint3 = :typeint WHERE customchar2 = :typename";
        $DB->execute($sql, array('typeint' => enrol_wsscol_plugin::INSTANCE_ETAT_AUTO, 'typename' => 'auto'));
        $DB->execute($sql, array('typeint' => enrol_wsscol_plugin::INSTANCE_ETAT_MANUAL, 'typename' => 'manual'));

        upgrade_plugin_savepoint(true, 2021100100, 'enrol', 'wsscol');
    }
    if ($oldversion < 2022091200) {
        // Define field searchgroups_name_ws to be added to enrol_wsscol_scolapps.
        $table = new xmldb_table('enrol_wsscol_scolapps');
        $fieldadd = new xmldb_field('searchgroups_name_ws', XMLDB_TYPE_CHAR, '16', null, null, null, null, 'searchgroups_name_ws');
        $fielddrop = new xmldb_field('getcoursesteacher_uri');
        // Conditionally launch add field searchgroups_name_ws.
        if (!$dbman->field_exists($table, $fieldadd)) {
            $dbman->add_field($table, $fieldadd);
        }
        if (!$dbman->field_exists($table, $fielddrop)) {
            $dbman->drop_field($table, $fielddrop);
        }

        // Wsscol savepoint reached.
        upgrade_plugin_savepoint(true, 2022091400, 'enrol', 'wsscol');
    }

    if ($oldversion < 2024070800) {

        // Define field status to be added to enrol_wsscol_scolapps.
        $table = new xmldb_table('enrol_wsscol_scolapps');
        $field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 1, 'getcoursesteacher_uri');

        // Conditionally launch add field status.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Wsscol savepoint reached.
        upgrade_plugin_savepoint(true, 2024070800, 'enrol', 'wsscol');
    }
    return true;

    if ($oldversion < 2024070800) {

        // Define field role to be added to enrol_wsscol_scolapps.
        $table = new xmldb_table('enrol_wsscol_scolapps');
        $field = new xmldb_field('role', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, null, 'status');

        // Conditionally launch add field role.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Wsscol savepoint reached.
        upgrade_plugin_savepoint(true, 2024070800, 'enrol', 'wsscol');
    }

    // OLIVIER - upgrade pour ajouter les nouveaux champs de la table.
    if ($oldversion < 2026042106) {
        $table = new xmldb_table('enrol_wsscol_scolapps');

        // Add authurl field
        $field = new xmldb_field('authurl', XMLDB_TYPE_CHAR, '253', null, null, null, null, 'role');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add tokenmethod field
        $field = new xmldb_field('tokenmethod', XMLDB_TYPE_CHAR, '16', null, null, null, null, 'authurl');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add getstudents_periode field
        $field = new xmldb_field('getstudents_periode', XMLDB_TYPE_CHAR, '32', null, null, null, null,'getstudents_uri');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add getstudents_structure field
        $field = new xmldb_field('getstudents_structure', XMLDB_TYPE_CHAR, '32', null, null, null, null, 'getstudents_periode');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2026042106, 'enrol', 'wsscol');
    }

    // OLIVIER - upgrade 2 - garder le type d'inscription dans la table
    if ($oldversion < 2026043000) {
        $table = new xmldb_table('enrol_wsscol_scolapps');

        $field = new xmldb_field(
            'type', XMLDB_TYPE_CHAR, '32',
            null, false, null, null,
            'role'
        );
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2026043000, 'enrol', 'wsscol');
    }

    return true;
}
