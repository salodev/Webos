<?php

namespace Webos\Service\Server;

use Exception;
use salodev\Pcntl\Thread;
use salodev\IO\Stream;
use salodev\IO\ClientSocket;

class Master extends Base {
	
	static private $_host = null;
	
	static private $_port = 0;
	
	static private $_lastPort = 0;
	
	static private $_userServices = [];
	
	static private $_masterTokenSeed = null;
	
	static public function Register(UserService $userService): void {
		self::$_userServices[$userService->userName] = $userService;
	}
	
	static public function Get(string $userName): UserService {
		if (!isset(self::$_userServices[$userName])) {
			throw new Exception('User info not found');
		}
		return self::$_userServices[$userName];
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
	
	static public function Check($userName) {
		static::Log("looking for '{$userName}' service info...");
		try {
			$userService = self::Get($userName);
		} catch (Exception $e) {
			static::Log("NOT FOUND\n");
			return false;
		}
		static::Log("OK\n");
		
		try {
			static::Log("making connection test for {$userName} in port no:{$userService->port}...", 'debug');
			$socket = ClientSocket::Create($userService->host, $userService->port, 0.5);
			$socket->close();
			static::Log("OK\n");
		} catch (Exception $e) {
			static::Log("TIMED OUT (0.5s)\n");
			return false;
		}
		return true;
	}
	
	static public function Create(UserService $userService) {
		
		// If user exists returns info.		
		if (self::Check($userService->userName)) {
			$userService = self::Get($userService->userName);
			return [
				'port'  => $userService->port,
				'token' => $userService->token,
			];
		}
		
		// If not, we need create a new service.
		
		$userService->port = self::_SelectNewPort($userService->port);
		
		//generate a token;
		$userService->token       = md5(time() . Thread::GetPid());
		$userService->masterToken = md5(time() . Thread::GetPid() . static::GetMasterTokenSeed());
		
		// Spawn service for user.
		$userService->host = self::$_host;		
		self::CreateViaFork($userService);
		
		// Store user info.
		self::Register($userService);
		static::Log("service information stored\n");
		
		// And retrieve it.
		return [
			'port'  => $userService->port,
			'token' => $userService->token,
		];
	}
	
	static private function CreateViaFork(UserService $userService) {
		static::Log("spawing via fork for '{$userService->userName}' in port {$userService->port}\n");

		$childProcess = Thread::Fork(function() use ($userService) {
			/**
			 * Incoming connection must be closed from child.
			 */
			$connection = static::$incomingConnection;
			if ($connection instanceof \salodev\IO\Socket && $connection->isValidResource()) {
				$connection->close();
			}
			
			/**
			 * Clear unnecesary references list.
			 * Avoid close master service connections.
			 */
			Stream::ClearIntancesList();
			
			/**
			 * So we are ready for start new service
			 */
			User::Start($userService);
		});
		$userService->created = microtime(true);
		$userService->setChildProcess($childProcess);
		
		static::Log("waiting service availability...");
		if (!$userService->getClient()->waitForService()) {
			static::Log("ERROR\n");
			throw new Exception('Service could not be spawned');
		}
		static::Log("OK\n");
		
		static::Log("SERVICE IS READY FOR USE\n");
	}
	
	static public function Remove($userName) {
		$userService = self::Get($userName);
		$userService->getChildProcess()->kill();
		unset(self::$_userServices[$userName]);
		return true;
	}
	
	static public function Restart($userName) {
		$userService = self::Get($userName);
		if (!$userService) {
			throw new Exception('User service not found');
		}
		$child = $userService->getChildProcess();
		$child->kill();
		$child->wait();
		self::CreateViaFork($userService);
		return true;
	}
	
	static public function Run(string $host = '127.0.0.1', int $port = 3000):void {
		self::$_host = $host;
		self::$_port = $port;
		self::$_lastPort = $port;
		
		static::RegisterActionHandler('create', function(array $data) {
			if (empty($data['userName'])) {
				throw new Exception('Missing userName param');
			}
			if (empty($data['applicationName'])) {
				throw new Exception('Missing applicationName param');
			}
			$userService					 = new UserService;
			$userService->userName			 = $data['userName'         ];
			$userService->applicationName	 = $data['applicationName'  ];
			$userService->applicationParams	 = $data['applicationParams'] ?? [];
			$userService->port				 = $data['port'             ] ?? null;
			$userService->userAgent			 = $data['userAgent'        ] ?? '';
			return self::Create($userService);
		});
		
		static::RegisterActionHandler('remove', function(array $data) {
			if (empty($data['userName'])) {
				throw new Exception('Missing userName param');
			}
			return self::Remove($data['userName']);
		});
		
		static::RegisterActionHandler('restart', function(array $data) {
			if (empty($data['userName'])) {
				throw new Exception('Missing userName param');
			}
			return self::Restart($data['userName']);
		});
		
		static::RegisterActionHandler('list', function() {
			$rs = [];
			foreach(self::$_userServices as $userName => $userService) {
				$rs[] = [
					'userName' => $userName,
					'port'     => $userService->port,
					'token'    => $userService->token,
					'pid'      => $userService->getChildProcess()->getPid(),
					'created'  => $userService->created,
				];
			}
			return $rs;
		});
		
		static::RegisterActionHandler('listCommands', function() {
			return static::GetActionsList();
		});
		
		parent::Run($host, $port);
	}
	
	static public function SetMasterTokenSeed(string $seed) {
		static::$_masterTokenSeed = $seed;
	}
	
	static public function GetMasterTokenSeed(): string {
		if (static::$_masterTokenSeed == null) {
			static::$_masterTokenSeed = 'master_seed_' . rand(1000, 9999);
		}
		return static::$_masterTokenSeed;
	}
}

