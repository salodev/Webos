<?php

namespace Webos\Service\NetworkService;

use Webos\WorkSpace;
use Webos\SystemInterface;
use Webos\WorkSpaceHandlers\Instance   as InstanceHandler;
use Webos\WorkSpaceHandlers\FileSystem as FileSystemHandler;
use salodev\Pcntl\Thread;

class UserServer extends Server {
	
	/**
	 *
	 * @var string 
	 */
	static private $_masterToken = null;
	
	/**
	 *
	 * @var \Webos\System
	 */
	static public $system = null;
	
	/**
	 *
	 * @var \Webos\SystemInterface;
	 */
	static public $interface = null;
	
	/**
	 *
	 * @var string;
	 */
	static private $_username = null;
	
	static public function Boot(string $username, bool $loadStoredWorkSpace = false, array $metadata = []) {
		static::$_username = $username;
		static::$interface = new SystemInterface();
		static::$system    = self::$interface->getSystemInstance();
		
		static::$system->setWorkSpaceHandler(new InstanceHandler(static::$system));
		
		if ($loadStoredWorkSpace) {
			$storageHandler = new FileSystemHandler(static::$system);
			$workSpace = $storageHandler->load($username);
			static::$system->setWorkSpace($workSpace);
		} else {
			static::$system->loadWorkSpace($username);
		}
	}
	
	static public function RegisterActionHandlers() {
		
		static::RegisterActionHandler('interface', function(array $data = []) {
			if (empty($data[0])) {
				throw new \Exception('Missing fist param');
			}
			$methodName = $data[0];
			$params     = $data[1];
			$validMethods = [
				'renderAll',
				'action',
				'debug',
				'getOutputStream',
				'getFilestoreDirectory',
				'getMediaContent',
				'setViewportSize'
			];
			if (!in_array($methodName, $validMethods)) {
				throw new Exception('Only valid mehtods: ' . implode(', ', $validMethods));
			}
			if ($methodName != 'renderAll') {
				// return $data;
			}
			return call_user_func_array([static::$interface, $methodName], $params);
			
		});
	
		static::RegisterActionHandler('storeWorkSpace', function () {
			static::CheckMasterToken();
			static::StoreWorkSpace();
		});
		
		static::RegisterActionHandler('testWorkSpace', function(array $data) {
			static::CheckMasterToken();
			if (empty($data['userName'])) {
				throw new \Exception('Missing userName parameter');
			}
			$username = $data['userName'];
						
			$interface = new SystemInterface();
			$system    = $interface->getSystemInstance();
			$system->setWorkSpaceHandler(new FileSystemHandler($system));
			$workSpace = $system->loadWorkSpace($username);
			$workSpace->renderAll();
			return true;
		});
		
		static::RegisterActionHandler('kill', function () {
			static::CheckMasterToken();
			die();
		});
	}
	
	static public function StartApplication(string $name, array $params = [], string $userAgent = null): bool {
		$ws = static::GetWorkSpace();
		if ($userAgent !== null) {
			$ws->checkUserAgent($userAgent);
		}
		$ws->startApplication($name, $params);		
		static::GetWorkSpace()->startApplication($name, $params);
		return true;
	}
	
	static public function GetWorkSpace(): WorkSpace {
		return static::$system->getWorkSpace();
	}
	
	static public function Start(ServiceAuthorization $authorization) {
		
		/**
		 *  It makes cancellabe by CTRL+C signal
		 */
		Thread::SetSignalHandler(SIGINT, function($signo) {
			die();
		});
		
		/*register_shutdown_function(function () {
			static::StoreWorkSpace();
			try {
				\salodev\Pcntl\Thread::CloseAllStreams();
			} catch (\Exception $e) {
				
			}
		});*/
		
		static::Boot($authorization->userName, $authorization->loadStoredWorkSpace);
		static::StartApplication($authorization->applicationName, $authorization->applicationParams, $authorization->userAgent);
		static::SetToken($authorization->token);
		static::SetMasterToken($authorization->masterToken);
		static::RegisterActionHandlers();
		static::Run($authorization->host, $authorization->port);
	}
	
	static public function SetMasterToken(string $token):void {
		static::$_masterToken = $token;
	}
	
	static public function CheckMasterToken(): bool {
		$requestData = static::GetLastRequest();
		if (!isset($requestData['masterToken']) || empty($requestData['masterToken'])) {
			throw new \Exception('Missing masterToken');
		}
		
		if ($requestData['masterToken'] != static::$_masterToken) {
			throw new \Exception('Invalid masterToken');
		}
		
		return true;
	}
	
	static public function StoreWorkSpace() {
		$workSpaceHandler = new \Webos\WorkSpaceHandlers\FileSystem(static::$system);
		$workSpace = static::$system->getWorkSpace();
		$workSpaceHandler->store($workSpace);
	}
}