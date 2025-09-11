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
 * @package block_wsscol
 * @author Serge FELIX<serge.felix@univ-lyon2.fr>
 * @copyright Université Lumière Lyon 2  {@link http://www.univ-lyon2.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once("$CFG->libdir/clilib.php");

// Now get cli options.
list($options, $unrecognized) = cli_get_params(array('verbose' => false, 'help' => false, 'delete' =>false, 'test' =>false), array('v' => 'verbose', 'h' => 'help', 'd' => 'delete', 't' => 'test'));
if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}
if ($options['help']) {
    $help =
            "Update the idnumber of all groups create by wsscol plugin. The new idnumber is hardcode. For me, but could be reuse. 

Options:
-v, --verbose         Print verbose progress information
-h, --help            Print out this help
-d, --delete          Delete group
-t, --test            Test mode : no action, disable delete and enable verbose

Example:
\$ sudo -u www-data /usr/bin/php enrol/wsscol/cli/bulk_update_groups.php
";

    echo $help;
    die;
}
if (!enrol_is_enabled('wsscol')) {
    cli_error('enrol_wsscol plugin is disabled, synchronisation stopped', 2);
}
if (empty($options['test'])) {
    $test = false;
    if (empty($options['verbose'])) {
        $trace = new null_progress_trace();
    } else {
        $trace = new text_progress_trace();
    }
    if (empty($options['delete'])) {
        $delete = false;
    } else {
        $delete = true;
    }
} else {
    $trace = new text_progress_trace();
    $test = true;
    $delete = false;
}
$plugin = enrol_get_plugin('wsscol');
$result = $plugin->bulk_update_groups($trace,$test,$delete);
$plugin->send_expiry_notifications($trace);

exit($result);
