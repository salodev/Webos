<?php

namespace Webos\Implementations\Service\NetworkService;

use Exception;
use salodev\Pcntl\Thread;
use salodev\IO\Stream;
use salodev\IO\ClientSocket;
use Webos\Webos;

class MasterServer extends Server {
	
	static private $_host = null;
	
	static private $_port = 0;
	
	static private $_lastPort = 0;
	
	static private $_userName = 'root';
	
	static protected $_token = 'root';
	
	/**
	 *
	 * @var ServiceAuthorization[] 
	 */
	static private $_userServices = [];
	
	static private $_masterTokenSeed = null;
	
	static public function SetHost(string $host): void {
		static::$_host = $host;
	}
	
	static public function SetPort(int $port): void {
		static::$_port = $port;
	}
	
	static public function SetUserName(string $userName): void {
		static::$_userName = $userName;
	}
	
	static public function GetHost(): string {
		return static::$_host;
	}
	
	static public function GetPort(): int {
		return static::$_port;
	}
	
	static public function GetUserName(): string {
		return static::$_userName;
	}
	
	static public function Register(ServiceAuthorization $authorization): void {
		self::$_userServices[$authorization->userName] = $authorization;
	}
	
	static public function Get(string $userName): ServiceAuthorization {
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
		static::Log("looking for '{$userName}' service info...", 'info');
		try {
			$authorization = self::Get($userName);
		} catch (Exception $e) {
			static::Log("NOT FOUND\n", 'info');
			return false;
		}
		
		try {
			static::Log("making connection test for {$userName} in port no:{$authorization->port}...", 'debug');
			$socket = ClientSocket::Create($authorization->host, $authorization->port, 0.5);
			$socket->close();
			static::Log("OK\n");
		} catch (Exception $e) {
			static::Log("TIMED OUT (0.5s)\n", 'info');
			return false;
		}
		return true;
	}
	
	static public function Create(ServiceAuthorization $authorization, array $authParams = []) {
		
		Webos::Authenticate($authorization->userName, $authParams);
		
		/**
		 * If user exists returns info.
		 */
		if (self::Check($authorization->userName)) {
			$authorization = self::Get($authorization->userName);			
			return [
				'host'  => $authorization->host,
				'port'  => $authorization->port,
				'token' => $authorization->token,
			];
		}
		
		/**
		 * If not, we need create a new service.
		 */
		static::Log("Creating new service", 'info');
		$authorization->port = self::_SelectNewPort($authorization->port);
		static::Log("port {$authorization->port}", 'info');
		
		/**
		 * generate a token;
		 */
		$authorization->token       = md5(time() . Thread::GetPid());
		$authorization->masterToken = md5(time() . Thread::GetPid() . static::GetMasterTokenSeed());
		
		/**
		 * Spawn service for user.
		 */
		$authorization->host = self::$_host;		
		static::Log("port {$authorization->port}", 'info');
		static::Log("Forking..", 'info');
		self::CreateViaFork($authorization);
		
		static::Log("Registering..", 'info');
		
		/**
		 * Store user info.
		 */
		self::Register($authorization);
		static::Log("service information stored\n");
		
		/**
		 * And retrieve it.
		 */
		return [
			'host'  => $authorization->host,
			'port'  => $authorization->port,
			'token' => $authorization->token,
		];
	}
	
	static private function CreateViaFork(ServiceAuthorization $authorization) {
		static::Log("spawing via fork for '{$authorization->userName}' in port {$authorization->port}\n");

		$childProcess = Thread::Fork(function() use ($authorization) {
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
			UserServer::Start($authorization);
		});
		$authorization->created = microtime(true);
		$authorization->setChildProcess($childProcess);
		
		static::Log("waiting service availability...");
		if (!$authorization->getClient()->waitForService()) {
			static::Log("ERROR\n");
			throw new Exception('Service could not be spawned');
		}
		static::Log("OK\n");
		
		static::Log("SERVICE IS READY FOR USE\n");
	}
	
	static public function Remove($userName) {
		$authoriztion = self::Get($userName);
		$authoriztion->getChildProcess()->kill();
		
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
		
		Thread::SetSignalHandler(SIGINT, function ($sigNumber) {
			foreach(static::$_userServices as $authorization) {
				$child = $authorization->getChildProcess();
				static::LogInfo("Signaling Interrupt to Service Authorization: User={$authorization->userName}, Pid={$child->getPid()}, Service={$authorization->host}:{$authorization->port} ...");
				$child->stop();
				$child->wait();
			}
			die();
		});
		
		self::$_host = $host;
		self::$_port = $port;
		self::$_lastPort = $port;
		
		static::RegisterActionHandler('create', function(array $data) {
			if (empty($data['userName'])) {
				throw new Exception('Missing userName param');
			}
			if (empty($data['authParams'])) {
				throw new Eception('Missing auth params');
			}
			if (!is_array($data['authParams'])) {
				throw new Exception('Auth params must be an object');
			}
			$authorization					  = new ServiceAuthorization;
			$authorization->userName          = $data['userName'         ];
			$authorization->port              = $data['port'             ] ?? null;
			$authorization->userAgent         = $data['userAgent'        ] ?? '';
			return self::Create($authorization, $data['authParams']);
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

