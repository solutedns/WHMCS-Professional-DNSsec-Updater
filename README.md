# DNSsec Updater for WHMCS

This is an example of how to use the hooks functionality in **SoluteDNS for WHMCS - Professional Edition** to automatically update the DNSsec keys with your registrar(s) utilizing the Metaregistrar [PHP EPP Client](https://github.com/metaregistrar/php-epp-client).

#### Supported Registrars:
  - Key Systems RRPPROXY
  - Metaregistrar
  - Openprovider
  - Donuts
  - SIDN (.nl)
  - dotAmsterdam
  - EurID (.eu)
  - DNS Belgium (.be .vlaanderen .brussels)
  - .CO.NL
  - dotFRL
  - IIS (.nu and .se)
  - CarDNS (.hr)
  - Nic.AT (.at)
  - Switch (.ch)
  - Ficora (.fi)
  - DNS.PT (.pt)
  - Norid (.no)
  - Arnes (.si)

## Getting Started

  - Upload the files to the __lib/Custom SoluteDNS__ addon directory.
  - Open the __Request.php__ file and add all required registrar configuration settings.

#### Example
  ```
	$api['metaregistrar'] = [
		'name'		=> 'Metaregistrar',
		'interface'	=> 'metaregEppConnection',
       	'hostname'	=> 'ssl://eppltest1.metaregistrar.com',
       	'port'		=> '7000',
        'username'	=> 'xxxxxxxxx',
       	'password'	=> 'xxxxxxxxx',
	];
	$api['openprovider'] = [
		'name'		=> 'OpenProvider',
		'interface'	=> 'openproviderEppConnection',
       	'hostname'	=> 'https://epptest.openprovider.eu',
       	'port'		=> '443',
        'username'	=> 'xxxxxxxxx',
       	'password'	=> 'xxxxxxxxx',
	];
```