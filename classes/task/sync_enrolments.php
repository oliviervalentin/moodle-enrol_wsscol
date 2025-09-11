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
 * Task to sync all wsscol enrolment methods
 *
 * @package enrol_wsscol
 * @author Serge FELIX <serge.felix@univ-lyon2.fr>
 * @copyright Université Lumière Lyon 2  {@link http://www.univ-lyon2.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_wsscol\task;

use core\task\scheduled_task;
use text_progress_trace;

class sync_enrolments extends scheduled_task {

    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('syncenrolmentstask', 'enrol_wsscol');
    }

    /**
     * Run task for syncing enrolments.
     */
    public function execute() {
        $enrol = enrol_get_plugin('wsscol');
        $trace = new text_progress_trace();
        if (!enrol_is_enabled('wsscol')) {
            $trace->output('Plugin not enabled');
            return;
        }
        $enrol->sync_enrolments($trace);
        $trace->finished();
    }

}
