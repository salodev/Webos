<?php

namespace Webos\Service\Master;
use Exception;
use Webos\Service\Server as BaseServer;
use Webos\Service\Client;
use Webos\Service\User\Server as UserServer;
use Webos\Log;
use salodev\Thread;

class Server {
	
	static private $_host = null;
	
	static private $_port = 0;
	
	static private $_lastPort = 0;
	
	static private $_usersInfo = [];
	
	static public function RegisterUserInfo($userName, $applicationName, $userPort, $userToken) {
		self::$_usersInfo[$userName] = [
			'port'  => $userPort,
			'token' => $userToken,
			'applicationName' => $applicationName,
		];
	}
	
	static public function GetUserInfo($userName) {
		return self::$_usersInfo[$userName] ?? false;
	}
	
	static private function _SelectNewPort($userPort) {
		if ($userPort) {
			self::$_lastPort = $userPort;
		} else {
			self::$_lastPort++;

			$userPort = self::$_lastPort;
		}
		return $userPort;
	}
	
	static public function CreateUserService($userName, $applicationName, $userPort = null) {
		
		// If user exists returns info.
		$userInfo = self::GetUserInfo($userName);
		if ($userInfo) {
			return $userInfo;
		}
		
		// If not, we need create a new service.
		
		$userPort = self::_SelectNewPort($userPort);
		
		//generate a token;
		$userToken = md5(time().getmypid());
		
		// Spawn service for user.
		$host = self::$_host;
		Thread::Fork(function() use ($userName, $userPort, $host) {
			UserServer::Listen($host, $userPort, $userName);
		});
		
		sleep(2); // give time to start
		
		// Spawn application into created service
		$client = new Client($userToken, self::$_host, $userPort);
		$client->call('startApplication', [
			'name' => $applicationName,
		]);		
		
		// Store user info.
		self::RegisterUserInfo($userName, $applicationName, $userPort, $userToken);
		
		// And retrieve it.
		return self::GetUserInfo($userName);
	}
	
	static public function Listen(string $host = '127.0.0.1', int $port = 3000) {
		self::$_host = $host;
		self::$_port = $port;
		self::$_lastPort = $port;
		
		BaseServer::RegisterActionHandler('create', function(array $data) {
			if (empty($data['userName'])) {
				throw new Exception('Missing userName param');
			}
			if (empty($data['applicationName'])) {
				throw new Exception('Missing applicationName param');
			}
			return self::CreateUserService($data['userName'], $data['applicationName'], $data['port'] ?? null);
		});
		BaseServer::Listen($host, $port);
	}
}

