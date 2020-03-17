<?php

namespace Webos\Service\Server;

use salodev\Debug\ObjectInspector;
use Webos\WorkSpace;
use Webos\SystemInterface;
use Webos\WorkSpaceHandlers\Instance   as InstanceHandler;
use Webos\WorkSpaceHandlers\FileSystem as FileSystemHandler;
use Webos\Service\Server\Base;

class User extends Base {
	
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
		
		static::RegisterActionHandler('renderAll', function(array $data) {
			return static::$interface->renderAll();
		});
		
		static::RegisterActionHandler('action', function(array $data) {
			$actionName = $data['name'      ];
			$objectID   = $data['objectID'  ];
			$parameters = $data['parameters'] ?? [];
			$ignoreUpdateObject = $data['ignoreUpdateObject'] ?? false;
			return static::$interface->action($actionName, $objectID, $parameters, $ignoreUpdateObject);
		});
		
		static::RegisterActionHandler('debug', function(array $data) {
			return ObjectInspector::inspect(static::$interface, $data['path'], true);
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
	
	static public function StartApplication(string $name, array $params = [], string $userAgent = ''): bool {
		$ws = static::GetWorkSpace();
		$ws->checkUserAgent($userAgent);
		$ws->startApplication($name, $params);		
		static::GetWorkSpace()->startApplication($name, $params);
		return true;
	}
	
	static public function GetWorkSpace(): WorkSpace {
		return static::$system->getWorkSpace();
	}
	
	static public function Start(UserService $userService) {
		register_shutdown_function(function () {
			static::StoreWorkSpace();
			try {
				\salodev\Pcntl\Thread::CloseAllStreams();
			} catch (\Exception $e) {
				
			}
		});
		static::Boot($userService->userName, $userService->loadStoredWorkSpace);
		static::StartApplication($userService->applicationName, $userService->applicationParams, $userService->userAgent);
		static::SetToken($userService->token);
		static::SetMasterToken($userService->masterToken);
		static::RegisterActionHandlers();
		static::Run($userService->host, $userService->port);
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