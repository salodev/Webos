<?php

namespace Webos\Service\Server;

use Exception;
use salodev\Debug\ObjectInspector;
use Webos\WorkSpace;
use Webos\SystemInterface;
use Webos\WorkSpaceHandlers\Instance as InstanceHandler;
use Webos\FrontEnd\Page;
use Webos\Service\Server\Base;

class User extends Base {
	
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
	
	static public function Boot(string $username) {
		self::$_username = $username;
		self::$interface = new SystemInterface();
		self::$system = self::$interface->getSystemInstance();
		
		self::$system->setWorkSpaceHandler(new InstanceHandler(self::$system));
		
		self::$system->loadWorkSpace($username);
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
	}
	
	static public function StartApplication(string $name, array $params = []): bool {
		static::GetWorkSpace()->startApplication($name, $params);
		return true;
	}
	
	static public function GetWorkSpace(): WorkSpace {
		return static::$system->getWorkSpace();
	}
	
	static public function Listen($address, $port) {
		static::Listen($address, $port);
	}
	
	static public function Start($userName, $port, $host, $userToken, $applicationName, $applicationParams) {
		static::Boot($userName);
		static::StartApplication($applicationName, $applicationParams);
		static::SetToken($userToken);
		static::RegisterActionHandlers();
		static::Listen($host, $port);
	}
}