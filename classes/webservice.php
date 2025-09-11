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
 * abstract class to call webservices
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
abstract class webservice {
    protected $wshost = '';
    protected $wsuser = '';
    protected $wspassword = '';

    /**
     * @param int $wsid
     * @throws dml_exception
     */
    abstract protected function __construct(int $wsid);

    /**
     * getinstance get an instance of class webservice
     *
     * @param int $wsid
     * @return self
     */
    abstract public static function getinstance(int $wsid): self;

    /**
     * getfromws : get data from webservice
     *
     * @param string $uri
     * @param string $search
     * @return mixed the return of json_decode
     * @throws exception
     */
    protected function getfromws($uri) {
        $header = array('Content-Type: text/plain');
        $curl = new \curl();
        $curl->setHeader($header);
        if (!is_null($this->wsuser)) {
            $options['CURLOPT_USERPWD'] = $this->wsuser . ':' . $this->wspassword;
        }
	$options['CURLOPT_CONNECTTIMEOUT'] = 10;
	$options['CURLOPT_SSL_VERIFYHOST'] = 0;
	$options['CURLOPT_SSL_VERIFYPEER'] = 0;
        $url = $this->wshost . '/' . $uri;
        $resp = $curl->get($url, null, $options);
	$curlinfo = $curl->get_info();
        if ($curl->error) {
            throw new \exception('webservices error, ' . $curl->error, $curl->errno);
        }
        if (!in_array($curlinfo['http_code'], array(200, 204))) {
            throw new \exception('webservices '.$url.' not return valid http response, ' . $curlinfo['http_code']);
        }
        return json_decode($resp, true);
    }
}
