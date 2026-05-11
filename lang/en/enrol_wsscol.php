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
 * Strings for component 'enrol_wsscol', language 'en'.
 *
 * @package    enrol_wsscol
 */

$string['pluginname'] = 'Enrolment by webservices';
$string['pluginname_desc'] = 'The wsscol enrolment plugin allows teacher to enrol all student from an educational group via a webservice';
$string['searchlabel'] = 'code';
$string['searchlabel_desc'] = 'code to search';
$string['add_instances_title'] = 'Add multiple inscriptions instances';
$string['webservices_title'] = 'Webservices';
$string['webservices_desc'] = 'settings of webservices';
$string['syncenrolmentstask'] = 'Synchronise wsscol enrolments task';
$string['defaultrole'] = 'Default role assignment';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during wsscol enrolment';
$string['role'] = 'Default assigned role';

// Add instance.
$string['zeroresult'] = '0 result';
$string['searchstring'] = 'code';
$string['searchplaceholder'] = 'enter your code';
$string['label'] = 'choose a schooling app : ';
$string['code'] = 'code';

// Lib.
$string['customint2'] = 'schooling app';
$string['error_customint2'] = 'No webservices configured';
$string['bulk_enrolment_button'] = 'Bulk enrolment';

// Wsmanage.
$string['wsmanage_link'] = 'add/manage web services schooling app';

// Wsedit.
$string['wsedit_submitlabel'] = 'add webservice';
$string['wsmanage_title'] = 'Manage Web Services';
$string['wsedit_title'] = 'Add Web Services';
$string['confirm_delete_ws'] = 'Sure to delete this Web Service ?';
$string['add_ws_button'] = 'Add new Web Service';
$string[''] = '';
$string['type'] = 'School application type';
$string['type_help'] = 'Choose the type of school application. This information can be used to adapt the fields to fill in or the processing according to the targeted application type.';
$string['typegeneric'] = 'Generic';

$string['wsscol:manage'] = 'Manage user wsscol-enrolments.';
$string['wsscol:config'] = 'Add or edit enrol-wsscol instance in course.';

$string['ws_section_general'] = 'General settings';
$string['ws_section_connection'] = 'Webservice connection';
$string['ws_section_tokenauth'] = 'Token authentication (optional)';
$string['ws_section_students'] = 'Students retrieval';
$string['ws_section_mapping'] = 'ID mapping';
$string['ws_section_groupsearch'] = 'Group search (optional)';

$string['ws_name'] = 'Webservice name';
$string['ws_name_help'] = 'Short name to identify this webservice (name, program, period...).';
$string['ws_role'] = 'Assigned role';
$string['ws_role_help'] = 'Moodle role assigned to students enrolled via this webservice.';
$string['ws_host'] = 'Server URL';
$string['ws_host_help'] = 'Base URL of the server hosting the webservice.';
$string['ws_baseurl'] = 'API base path';
$string['ws_baseurl_help'] = 'Path to append after the server URL (example : api/ext/v1). Leave empty if full URL is in URIs.';
$string['ws_user'] = 'Username';
$string['ws_password'] = 'Password';
$string['ws_tokenmethod'] = 'Authentication method';
$string['ws_tokenmethod_help'] = 'Choose the authentication type. Leave "None" for basic HTTP auth.';
$string['ws_tokenmethod_none'] = 'None (basic auth)';
$string['ws_authurl'] = 'Token authentication URL';
$string['ws_authurl_help'] = 'URL of the authentication endpoint to obtain a token.';
$string['ws_tokenauth_desc'] = 'This section is optional. Only fill it if the webservice uses token authentication rather than basic HTTP authentication.';
$string['ws_getstudents_uri'] = 'Students retrieval URI';
$string['ws_getstudents_uri_help'] = 'URI called to retrieve the list of students. Use [search] for the group code, [periode] for the period, [structure] for the establishment code. Example for PEGASE : objet-formation/[structure]/[periode]/[search]/cursus';
$string['ws_getstudents_uri_desc'] = 'The URI supports these placeholders: <strong>[search]</strong> (group code entered by the teacher), <strong>[periode]</strong> (academic period), <strong>[structure]</strong> (establishment code).';
$string['ws_getstudents_periode'] = 'Period value [periode]';
$string['ws_getstudents_periode_help'] = 'Value to substitute for the [periode] placeholder in the URI (e.g. PERIODE-25-26).';
$string['ws_getstudents_structure'] = 'Establishment code value [structure]';
$string['ws_getstudents_structure_help'] = 'Value to substitute for the [structure] placeholder in the URI.';
$string['ws_getstudent_id_ws'] = 'ID field in WS response';
$string['ws_getstudent_id_ws_help'] = 'Name of the field returned by the webservice containing the student identifier.';
$string['ws_getstudent_id_local'] = 'Corresponding Moodle field';
$string['ws_getstudent_id_local_help'] = 'User profile field in Moodle to match against the identifier returned by the webservice.';
$string['ws_mapping_desc'] = 'Define the mapping between the identifier returned by the webservice and the Moodle user profile field.';
$string['ws_searchgroups_uri'] = 'Group search URI';
$string['ws_searchgroups_code_ws'] = 'Group code field in WS response';
$string['ws_searchgroups_name_ws'] = 'Group name field in WS response';
$string['ws_groupsearch_desc'] = 'This section is optional. Only fill it if the webservice provides a group search endpoint.';
$string['wsedit_title_add'] = 'Add a webservice';
$string['wsedit_title_edit'] = 'Edit a webservice';