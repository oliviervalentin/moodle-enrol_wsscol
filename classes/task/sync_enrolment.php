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
 * adhoc task to sync one wsscol enrolment method
 *
 * @package enrol_wsscol
 * @author Serge FELIX <serge.felix@univ-lyon2.fr>
 * @copyright Université Lumière Lyon 2  {@link http://www.univ-lyon2.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_wsscol\task;

use core\task\adhoc_task;
use text_progress_trace;

class sync_enrolment extends adhoc_task {
    public function execute() {
        global $DB;
        $trace = new text_progress_trace();
        $enrol = enrol_get_plugin('wsscol');
        $data = $this->get_custom_data();
        if ($data->instance) {
            $trace->output('Processing sync enrolment');
            $enrol->sync_enrolment($data->instance, $trace);
        } else {
            $trace->output('Aucune instance wsscol');
        }
        $trace->finished();
    }
}
