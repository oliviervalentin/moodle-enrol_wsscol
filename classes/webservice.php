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


    // AJOUT OLIVIER -  auth par token.
    /** @var string
     * The URL for authentication.
     */
    protected $authurl     = '';
    /** @var string
     * The method for token authentication : basic, CAS.
     */
    protected $tokenmethod = '';
    /** @var string|null
     * Token cache.
     */
    private $token = null;

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
     * OLIVIER ! modifs pour ajouter le token dans les headers.
     *
     * @param string $uri
     * @param string $search
     * @return mixed the return of json_decode
     * @throws exception
     */
    protected function getfromws($uri) {
        $token = $this->get_token();

        // DEBUGs pour voir le retour serveur !!!
        error_log('WSSCOL token value: ' . ($token ? substr($token, 0, 50) . '...' : 'NULL'));
        error_log('WSSCOL authurl: ' . $this->authurl);
        error_log('WSSCOL tokenmethod: ' . $this->tokenmethod);

        $url = $this->wshost . '/' . $uri;

        $ch = curl_init();

        $headers = ['Accept: application/json'];

        if ($token) {
            // Token auth — Bearer header.
            $headers[] = 'Authorization: Bearer ' . $token;
        } else if (!empty($this->wsuser)) {
            // Basic auth.
            curl_setopt($ch, CURLOPT_USERPWD, $this->wsuser . ':' . $this->wspassword);
        }

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => $headers,
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception('webservices error, ' . $error);
        }

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!in_array($httpcode, [200, 204])) {
            throw new \Exception('webservices ' . $url . ' not return valid http response, ' . $httpcode);
        }

        if (empty($response)) {
            return [];
        }

        return json_decode($response, true);
    }

    /**
     * OLIVIER - récup du token
     * Code récup. depuis mon enrol_pegase !
     */
    protected function get_token(): ?string {
        if (empty($this->authurl)) {
            return null;
        }

        if ($this->token !== null) {
            return $this->token;
        }

        if ($this->tokenmethod === 'cas') {
            // DO NOT USE http_build_query() with CAS server.
            $postfields = 'username=' . urlencode($this->wsuser)
                        . '&password=' . urlencode($this->wspassword)
                        . '&token=true';

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $this->authurl,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $postfields,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/x-www-form-urlencoded',
                ],
            ]);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new \Exception('CAS token auth failed: ' . $error);
            }

            $httpcode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headersize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            curl_close($ch);

            $body = trim(substr($response, $headersize));

            if ($httpcode !== 201 || empty($body)) {
                throw new \Exception('CAS token auth failed, HTTP ' . $httpcode);
            }

            $this->token = $body;
            return $this->token;
        }

        return null;
    }
}
