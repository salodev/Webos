<?php

namespace Webos\Service\Master;
use Exception;
use Webos\Service\Server as BaseServer;
use Webos\Service\Client;
use Webos\Log;
use salodev\Thread;
use salodev\Process;

class Server {
	
	static private $_lastPort = 0;
	
	static private $_usersInfo = [];
	
	static public function RegisterUserInfo($userName, $userPort, $userToken) {
		self::$_usersInfo[$userName] = [
			'port'  => $userPort,
			'token' => $userToken,
			'new'   => true,
		];
	}
	
	static public function GetUserInfo($userName) {
		return self::$_usersInfo[$userName] ?? false;
	}
	
	static public function CreateUserService($userName, $applicationName, $userPort = null) {
		
		$userInfo = self::GetUserInfo($userName);
		if ($userInfo) {
			$userInfo['new'] = false;
			return $userInfo;
		}
		
		if ($userPort) {
			self::$_lastPort = $userPort;
		} else {
			self::$_lastPort++;

			$userPort = self::$_lastPort;
		}

		$commandLine = "/var/www/sg/private/services/spawner.php --userPort={$userPort} --userName={$userName}";
		Log::write($commandLine);
		shell_exec($commandLine);

		//generate a token;
		$userToken = md5(time().getmypid());
		
		self::RegisterUserInfo($userName, $userPort, $userToken);
		
		return self::GetUserInfo($userName);
	}
	
	static public function Listen(string $host = '127.0.0.1', int $port = 3000) {
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

