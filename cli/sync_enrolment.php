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
 * CLI update for wsscol enrolments, use for debugging or immediate update
 * of all courses.
 *
 * Notes:
 *   - it is required to use the web server account when executing PHP CLI scripts
 *   - you need to change the "www-data" to match the apache user account
 *   - use "su" if "sudo" not available
 *
 * @package enrol_wsscol
 * @author Serge FELIX <serge.felix@univ-lyon2.fr>
 * @copyright Université Lumière Lyon 2  {@link http://www.univ-lyon2.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once("$CFG->libdir/clilib.php");

// Now get cli options.
list($options, $unrecognized) =
        cli_get_params(array('verbose' => false, 'help' => false, 'instanceid' => null),
                array('v' => 'verbose', 'h' => 'help', 'i' => 'instanceid'));
if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}
if ($options['help']) {
    $help =
            "sync one wsscol instance.

Options:
-v, --verbose         Print verbose progress information
-h, --help            Print out this help

Example:
\$ sudo -u www-data /usr/bin/php enrol/wsscol/cli/sync_enrolment.php --instanceid=525
";

    echo $help;
    die;
}
if (!enrol_is_enabled('wsscol')) {
    cli_error('enrol_wsscol plugin is disabled, synchronisation stopped', 2);
}
if (empty($options['verbose'])) {
    $trace = new null_progress_trace();
} else {
    $trace = new text_progress_trace();
}
if (empty($options['instanceid'])) {
    cli_error('enter an instanceid', 2);
}
$instance = $DB->get_record('enrol', ['id' => intval($options['instanceid'])], '*', MUST_EXIST);
if (!$instance) {
    cli_error('instanceid not exist', 2);
}
$plugin = enrol_get_plugin('wsscol');
$result = $plugin->sync_enrolment($instance, $trace);
$plugin->send_expiry_notifications($trace);

exit($result);
