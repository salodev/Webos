<?php

namespace Webos\Service;
use Webos\Service\Client;

class Api {
	
	static public function CreateUserService($userName, $applicationName) {
		$client = new Client('root', '127.0.0.1', 3000);
		$ret = $client->call('create', [
			'userName'=>"{$userName}_0",
			'applicationName'=> $applicationName,
		]);
			
		$port = $ret['port'];
		$token = $ret['token'];
		$new   = $ret['new'];
		$client = new Client($token, '127.0.0.1', $port);
		if ($new) {
			$client->call('startApplication', [
				'name' => $applicationName,
				'params' => [],
			]);
		}
		
		return $client;
	}
}