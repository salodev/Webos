<?php

namespace Webos\Service\Server;

use Exception;
use salodev\Debug\ObjectInspector;
use Webos\WorkSpace;
use Webos\SystemInterface;
use Webos\WorkSpaceHandlers\Instance as InstanceHandler;
use Webos\FrontEnd\Page;
use Webos\Service\Server\Base as BaseServer;

class User {
	
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
	
	static public function SetToken(string $token):void {
		BaseServer::SetToken($token);
	}
	
	static public function Prepare(string $username) {
		self::$_username = $username;
		self::$interface = new SystemInterface();
		self::$system = self::$interface->getSystemInstance();
		
		self::$system->setWorkSpaceHandler(new InstanceHandler(self::$system));
		
		self::$system->loadWorkSpace($username);
	}
	
	static public function RegisterActionHandlers() {
		BaseServer::RegisterActionHandler('startApplication', function(array $data) {
			if (empty($data['name'])) {
				throw new Exception('Missing name param');
			}
			$name   = $data['name'  ];
			$params = $data['params'] ?? [];
			self::GetWorkSpace()->startApplication($name, $params);
			return true;
		});
		
		BaseServer::RegisterActionHandler('renderAll', function(array $data) {
			return self::$interface->renderAll();
		});
		
		BaseServer::RegisterActionHandler('action', function(array $data) {
			$actionName = $data['name'      ];
			$objectID   = $data['objectID'  ];
			$parameters = $data['parameters'] ?? [];
			$ignoreUpdateObject = $data['ignoreUpdateObject'] ?? false;
			return self::$interface->action($actionName, $objectID, $parameters, $ignoreUpdateObject);
		});
		
		BaseServer::RegisterActionHandler('debug', function(array $data) {
			return ObjectInspector::inspect(self::$interface, $data['path'], true);
		});
	}
	
	static public function GetWorkSpace(): WorkSpace {
		return self::$system->getWorkSpace();
	}
	
	static public function Listen($address, $port, $username) {
		self::Prepare($username);
		self::RegisterActionHandlers();
		BaseServer::Listen($address, $port);
	}
}