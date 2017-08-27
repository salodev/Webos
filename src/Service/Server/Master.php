<?php

namespace Webos\Service\Server;

use Exception;
use Webos\Service\Server\Base as BaseServer;
use Webos\Service\Server\User as UserServer;
use Webos\Service\Client;
use salodev\Thread;
use salodev\Child;
use salodev\ClientSocket;

class Master {
	
	static private $_host = null;
	
	static private $_port = 0;
	
	static private $_lastPort = 0;
	
	static private $_usersInfo = [];
	
	static public function RegisterUserInfo($name, $applicationName, $port, $token, Child $child) {
		self::$_usersInfo[$name] = [
			'child'   => $child,
			'port'    => $port,
			'token'   => $token,
			'created' => microtime(true),
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
	
	static public function CheckUserService($userName) {
		echo "checking {$userName}...\n";
		$userInfo = self::GetUserInfo($userName);
		if (!$userInfo) {
			return false;
		}
		
		$port = $userInfo['port'];
		try {
			$socket = ClientSocket::Create('127.0.0.1', $port, 0.5);
			$socket->close();
		} catch (Exception $e) {
			return false;
		}
		return true;
	}
	
	static public function CreateUserService($userName, $applicationName, array $applicationParams = [], $userPort = null) {
		
		// If user exists returns info.		
		if (self::CheckUserService($userName)) {
			$userInfo = self::GetUserInfo($userName);
			return [
				'port'  => $userInfo['port' ],
				'token' => $userInfo['token'],
			];
		}
		
		// If not, we need create a new service.
		
		$userPort = self::_SelectNewPort($userPort);
		
		//generate a token;
		$userToken = md5(time() . Thread::GetPid());
		
		// Spawn service for user.
		$host = self::$_host;		
		self::_CreateUserService($userName, $userPort, $host, $userToken, $applicationName, $applicationParams);
		
		// And retrieve it.
		return [
			'port'  => $userPort,
			'token' => $userToken,
		];
	}
	
	static private function _CreateUserService($userName, $userPort, $host, $userToken, $applicationName, $applicationParams) {
		$childProcess = Thread::Fork(function() use ($userName, $userPort, $host, $userToken) {
			UserServer::SetToken($userToken);
			UserServer::Listen($host, $userPort, $userName);
		});
		
		// Spawn application into created service
		$client = new Client($userToken, self::$_host, $userPort);
		
		if (!$client->waitForService()) {
			throw new Exception('Service could not be spawned');
		}
		
		$client->call('startApplication', [
			'name'   => $applicationName,
			'params' => $applicationParams,
		]);
		
		// Store user info.
		self::RegisterUserInfo($userName, $applicationName, $userPort, $userToken, $childProcess);
	}
	
	static public function RemoveUserService($userName) {
		$userInfo = self::GetUserInfo($userName);
		if (!$userInfo) {
			throw new Exception('User service not found');
		}
		$userInfo['child']->kill();
		unset(self::$_usersInfo[$userName]);
		return true;
	}
	
	static public function RestartUserService($userName) {
		$userInfo = self::GetUserInfo($userName);
		if (!$userInfo) {
			throw new Exception('User service not found');
		}
		$child = $userInfo['child'];
		$child->kill();
		$child->wait();
		$userPort  = $userInfo['port'];
		$userToken = $userInfo['token'];
		$applicationName = $userInfo['applicationName'];
		$host      = '127.0.0.1';
		self::_CreateUserService($userName, $userPort, $host, $userToken, $applicationName);
		return true;
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
			return self::CreateUserService($data['userName'], $data['applicationName'], $data['applicationParams'] ?? [], $data['port'] ?? null);
		});
		
		BaseServer::RegisterActionHandler('remove', function(array $data) {
			if (empty($data['userName'])) {
				throw new Exception('Missing userName param');
			}
			return self::RemoveUserService($data['userName']);
		});
		
		BaseServer::RegisterActionHandler('restart', function(array $data) {
			if (empty($data['userName'])) {
				throw new Exception('Missing userName param');
			}
			return self::RestartUserService($data['userName']);
		});
		
		BaseServer::RegisterActionHandler('list', function() {
			$rs = [];
			foreach(self::$_usersInfo as $name => $userInfo) {
				$rs[] = [
					'name'    => $name,
					'port'    => $userInfo['port'   ],
					'token'   => $userInfo['token'  ],
					'pid'     => $userInfo['child'  ]->getPid(),
					'created' => $userInfo['created'],
				];
			}
			return $rs;
		});
		
		BaseServer::Listen($host, $port);
	}
}

