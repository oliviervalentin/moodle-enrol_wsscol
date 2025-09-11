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
 * class to call schoolapps webservices (extend enrol_wsscol_webservice, most generic class).
 *
 * @package enrol_wsscol
 * @author Serge FELIX <serge.felix@univ-lyon2.fr>
 * @copyright Université Lumière Lyon 2  {@link http://www.univ-lyon2.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_wsscol;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

/**
 * REST enrol_wsscol webservice client
 */
class schoolapp extends \enrol_wsscol\webservice {
    private static $instance = array();
    private $getstudentsuri = '';
    private $getstudentidws = '';
    private $getstudentidlocal = '';
    private $searchgroupsuri = '';
    private $searchgroupscodews = '';
    private $searchgroupsnamews = '';

    /**
     * @param int $wsid
     * @throws dml_exception
     */
    protected function __construct(int $wsid) {
        global $DB;
        $wsapp = $DB->get_record('enrol_wsscol_scolapps', array('id' => $wsid));
        if (!$wsapp) {
            throw new \Exception('No scolapps with id '. $wsid);
        }
        if (!$wsapp->wshost) {
            throw new \Exception('No wshost for scolapps with id '. $wsid);
        }
        $this->wshost = ($wsapp->baseurl) ? $wsapp->wshost . '/' . $wsapp->baseurl : $wsapp->wshost;
        $this->wsuser = $wsapp->wsuser;
        $this->wspassword = $wsapp->wspassword;
        if (!$wsapp->getstudents_uri) {
            throw new \Exception('No getstudents_uri for scolapps with id '. $wsid);
        }
        if (!$wsapp->getstudent_id_ws) {
            throw new \Exception('No getstudent_id_ws for scolapps with id '. $wsid);
        }
        if (!$wsapp->getstudent_id_local) {
            throw new \Exception('No getstudent_id_local for scolapps with id '. $wsid);
        }
        $this->getstudentsuri = $wsapp->getstudents_uri;
        $this->getstudentidws = $wsapp->getstudent_id_ws;
        $this->getstudentidlocal = $wsapp->getstudent_id_local;
        $this->searchgroupsuri = $wsapp->searchgroups_uri ?? NULL;
        $this->searchgroupscodews = $wsapp->searchgroups_code_ws ?? NULL;
        $this->searchgroupsnamews = $wsapp->searchgroups_name_ws ?? NULL;
    }
    
    /**
     * getinstance get an instance of enrol_wsscol_webservice
     *
     * @param int $wsid
     * @return self
     */
    public static function getinstance(int $wsid): \enrol_wsscol\webservice {
        if (key_exists($wsid, self::$instance)) {
            if (is_null(self::$instance[$wsid])) {
                self::$instance[$wsid] = new \enrol_wsscol\schoolapp($wsid);
            }
        } else {
            self::$instance[$wsid] = new \enrol_wsscol\schoolapp($wsid);
        }
        return self::$instance[$wsid];
    }

    /**
     * searchgroups : search Students Groups by name
     *
     * @param string $search code to search
     * @Return stdObject[] array of stdObject->id stdObject->name
     */
    public function search($search) {
        global $DB;
        $pattern = array('%\[search\]%');
        $replacement = array(rawurlencode($search));
        $uri = preg_replace($pattern, $replacement, $this->searchgroupsuri);
        $searchcodewem = $this->getfromws($uri);
        if ($searchcodewem == '') {
            return false;
        } else {
            $codeswem = array();
            $count = 0;
            foreach ($searchcodewem as $codewem) {
                $count++;
                $code = $codewem[$this->searchgroupscodews];
                $name = $codewem[$this->searchgroupsnamews];
                $codeswem[$code] = (object) ["code" => $code, "name" => $name];
                if ($count === 100) {
                    break;
                }
            }
            return $codeswem;
        }
    }

    /**
     * getstudents get students from course
     *
     * @param string $search course code to search
     * @Return mixed false or array of users : [userid] => array (id,lastname,firstname,email,username)
     */
    public function getstudents($search) {
        global $DB;
        $pattern = array('%\[search\]%');
        $replacement = array($search);
	$uri = preg_replace($pattern, $replacement, $this->getstudentsuri);
        try {
            $students = $this->getfromws($uri);
        } catch (Exception $e) {
            echo 'Exception : ', $e->getMessage(), "\n";
	}
        // Return must be an array. And an empty array is not a NULL value
        // We make no difference between a response with 0 result and a bad request (with a bad code).
        // The both respond a http 204 (in our university).
        // So use a different error code from 204 for a bad request code.
        // Do'nt know what to use yet, it's not a 400 error.
        if ($students == '') {
            return array();
        } else {
            $users = array();
            foreach ($students as $key => $val) {
                $userdb =
                        $DB->get_record('user', array($this->getstudentidlocal => $val[$this->getstudentidws]),
                                'id,lastname,firstname,email,username');
                if ($userdb) {
                    $users[$userdb->id] = $userdb;
                }
            }
            return $users;
        }
    }
}
