<?php
// This file is part of Moodle - http://moodle.org/
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Extend PHP SoapClient with some header information
 *
 * @package    mod_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Extend PHP SoapClient with some header information
 *
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace connector\lib;

class SigSoapClient extends \SoapClient {

    /**
     * Set app properties and soap headers
     *
     * @param string $wsdl
     * @param array $options
     */
    public function __construct($wsdl, $options = array()) {
        parent::__construct($wsdl, $options);
        $this->setSigHeaders();
    }

    /**
     * Set soap headers
     *
     * @throws Exception
     */
    private function setSigHeaders() {
        try {
            $timestamp = round(microtime(true) * 1000);
            $signdata = 'educonnector' . $timestamp;
            $cryptographer = new \connector\lib\Cryptographer();
            $privkey = $cryptographer->getPrivateKey();
            $pkeyid = openssl_get_privatekey($privkey);
            openssl_sign($signdata, $signature, $pkeyid);
            $signature = base64_encode($signature);
            openssl_free_key($pkeyid);
            $headers = array();
            $headers[] = new \SOAPHeader('http://webservices.edu_sharing.org', 'appId', 'educonnector');
            $headers[] = new \SOAPHeader('http://webservices.edu_sharing.org', 'timestamp', $timestamp);
            $headers[] = new \SOAPHeader('http://webservices.edu_sharing.org', 'signature', $signature);
            $headers[] = new \SOAPHeader('http://webservices.edu_sharing.org', 'signed', $signdata);
            parent::__setSoapHeaders($headers);
        } catch (Exception $e) {
            throw new \Exception('error_set_soap_headers' . $e->getMessage());
        }
    }
}
