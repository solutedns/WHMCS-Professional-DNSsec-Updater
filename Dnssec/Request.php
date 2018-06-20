<?php

namespace WHMCS\Module\Addon\SoluteDNS\Custom\Dnssec;

/**
 *               *** SoluteDNS DNSsec Updater for WHMCS ***
 *
 * @file        Hooks.php
 * @package     solutedns-dnssec-update-whmcs
 * @required    solutedns-pro-whmcs
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @license     GNU General Public License v3.0
 * @author      NetDistrict <info@netdistrict.net>
 * @sub-author  Metaregistrar
 * */
if (!defined("WHMCS")) {
	die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

require('lib/php-epp-client/autoloader.php');

use Metaregistrar\EPP\eppConnection;
use Metaregistrar\EPP\eppDomain;
use Metaregistrar\EPP\eppSecdns;
use Metaregistrar\EPP\eppDnssecUpdateDomainRequest;
use Metaregistrar\EPP\eppException;
use Metaregistrar\EPP\eppInfoDomainRequest;
use Metaregistrar\EPP\eppInfoDomainResponse;

class Request {

	/**
	 * Configuration
	 *
	 * @return array
	 */
	protected function config() {

		// Add configuration settings for each used/supported registrar below

		$api['metaregistrar'] = [
			'name' => 'MetaRegistrar',
			'interface' => 'metaregEppConnection',
			'hostname' => 'ssl://eppltest1.metaregistrar.com',
			'port' => '7000',
			'username' => 'xxxxxxxxx',
			'password' => 'xxxxxxxxx',
		];

		return $api;
	}

	/**
	 * Update DNSsec Keys
	 *
	 * @param array $vars
	 */
	public function Update($vars) {

		// Get index info
		$index = Capsule::table('mod_solutedns_zones')->where('domain', $vars['domain'])->first();

		// Check if it is a domain registration
		if ($index->type == 'd') {

			// Get domain info	
			$domaindetails = Capsule::table('tbldomains')->where('id', $index->local_id)->first();

			// If configuration is set for registrar
			if (is_array($this->config()[$domaindetails->registrar])) {

				try {

					// Create Connection to registrar
					$conn = new eppConnection();
					$conn->setHostname($this->config()[$domaindetails->registrar]['hostname']);
					$conn->setPort($this->config()[$domaindetails->registrar]['port']);
					$conn->setUsername($this->config()[$domaindetails->registrar]['username']);
					$conn->setPassword($this->config()[$domaindetails->registrar]['password']);

					if ($conn) {

						$conn->enableDnssec();

						if ($conn->login()) {

							$add = new eppDomain($vars['domain']);
							$sec = new eppSecdns();

							foreach ($vars['keys'] as $key) {

								if ($key['active'] == '1') {
									$flag = ($key['flag'] == 'ZSK') ? '256' : '257';
									$sec->setKey($flag, $key['algorithm'], $key['public_key']);
								}
							}

							$add->addSecdns($sec);
							$update = new eppDnssecUpdateDomainRequest($vars['domain'], $add);

							if ($response = $conn->request($update)) {
								logActivity('DnssecUpdate Hook: DNS Keys send to: ' . $this->config()[$domaindetails->registrar]['name'] . ' for domain: ' . $vars['domain']);
							}

							$conn->logout();
						}
					}
				} catch (eppException $e) {
					logActivity('DnssecUpdate Hook: ERROR: [' . $this->config()[$domaindetails->registrar]['name'] . '] ' . $e->getMessage());
				}
			}
		}
	}

	/**
	 * Delete DNSsec Keys
	 *
	 * @param array $vars
	 */
	public function Delete($vars) {

		// Get index info
		$index = Capsule::table('mod_solutedns_zones')->where('domain', $vars['domain'])->first();

		// Check if it is a domain registration
		if ($index->type == 'd') {

			// Get domain info	
			$domaindetails = Capsule::table('tbldomains')->where('id', $index->local_id)->first();

			// If configuration is set for registrar
			if (is_array($this->config()[$domaindetails->registrar])) {

				try {

					$conn = new eppConnection();
					$conn->setHostname($this->config()[$domaindetails->registrar]['hostname']);
					$conn->setPort($this->config()[$domaindetails->registrar]['port']);
					$conn->setUsername($this->config()[$domaindetails->registrar]['username']);
					$conn->setPassword($this->config()[$domaindetails->registrar]['password']);

					if ($conn) {

						$conn->enableDnssec();

						if ($conn->login()) {

							$dnssec = infodomain($conn, $vars['domain']);

							if (is_array($dnssec) && (count($dnssec) > 0)) {

								removednssec($conn, $vars['domain'], $dnssec);
								logActivity('DnssecUpdate Hook: DNS Keys removed at: ' . $this->config()[$domaindetails->registrar]['name'] . ' for domain: ' . $vars['domain']);
							}

							$conn->logout();
						}
					}
				} catch (eppException $e) {
					logActivity('DnssecUpdate Hook: ERROR: [' . $this->config()[$domaindetails->registrar]['name'] . '] ' . $e->getMessage());
				}
			}
		}
	}

	/**
	 * @param eppConnection $conn
	 * @param $domainname
	 * @param [eppSecdns] $dnssec
	 */
	protected function removednssec(eppConnection $conn, $domainname, $dnssec) {
		$domain = new eppDomain($domainname);
		$remove = new eppDomain($domainname);
		foreach ($dnssec as $secdns) {
			$remove->addSecdns($secdns);
		}
		$update = new eppDnssecUpdateDomainRequest($domain, null, $remove, null);
		if ($response = $conn->request($update)) {
			//echo $response->saveXML();
		}
	}

	/**
	 * @param $conn Metaregistrar\EPP\eppConnection
	 * @param $domainname string
	 * @return string
	 */
	protected function infodomain(eppConnection $conn, $domainname) {
		$info = new eppInfoDomainRequest(new eppDomain($domainname));
		if ($response = $conn->request($info)) {
			return $response->getKeydata();
		} else {
			//echo "ERROR retrieving domain info for $domainname\n";
		}
		return null;
	}

}
