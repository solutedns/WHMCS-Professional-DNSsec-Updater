<?php

namespace WHMCS\Module\Addon\SoluteDNS\Custom;

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
 * */
if (!defined("WHMCS")) {
	die("This file cannot be accessed directly");
}

use WHMCS\Module\Addon\SoluteDNS\Custom\Dnssec\Request;

/**
 * Custom Hooks
 */
class Hooks {

	/**
	 * Hook: AfterZoneCreation
	 *
	 * @param array $vars
	 */
	public function AfterZoneCreation($vars) {

		// Call DNSsec Update Script
		if (is_array($vars['dnssec'])) {
			$dnssec = new Request();
			$vars['dnssec']['domain'] = $vars['domain'];
			$dnssec->update($vars['dnssec']);
		}
	}

	/**
	 * Hook: AfterZoneCreation
	 *
	 * @param array $vars
	 */
	public function DnssecUpdate($vars) {

		// Call DNSsec Update Script
		$dnssec = new Request();
		if (is_array($vars['keys'])) {
			$dnssec->Update($vars);
		} else {
			$dnssec->Delete($vars);
		}
	}

}
