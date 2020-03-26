<?php

namespace Webos\Implementations\Service\NetworkService;

use Webos\Webos;
use Webos\SystemInterface;
use Webos\WorkSpaceHandlers\Instance   as InstanceHandler;
use Webos\WorkSpaceHandlers\FileSystem as FileSystemHandler;
use salodev\Pcntl\Thread;

/**
 * This class provides a complete authorized user service server
 */
class UserServer extends Server {
	
	/**
	 *
	 * @var string 
	 */
	static private $_masterToken = null;

	/**
	 *
	 * @var \Webos\SystemInterface;
	 */
	static public $interface = null;
	
	static public function Boot(string $userName, string $userAgent) {
		static::$interface = new SystemInterface();
		static::$interface->run($userName, Webos::GetApplicationName(), Webos::GetApplicationParams(), $userAgent, InstanceHandler::class);
	}
	
	static public function RegisterActionHandlers() {
		
		/**
		 * This action allow call remotelly certain interface actions.
		 */
		static::RegisterActionHandler('interface', function(array $data = []) {
			
			/**
			 * No method name nothing to do..
			 */
			if (empty($data[0])) {
				throw new \Exception('Missing fist param');
			}
			
			/**
			 * Get the method name
			 */
			$methodName = $data[0];
			
			/**
			 * And the method parameters
			 */
			$params = $data[1];
			
			/**
			 * Methods whitelist.
			 */
			$validMethods = [
				'renderAll',
				'action',
				'debug',
				'getOutputStream',
				'getFilestoreDirectory',
				'getMediaContent',
				'setViewportSize'
			];
			
			/**
			 * Validate
			 */
			if (!in_array($methodName, $validMethods)) {
				throw new \Exception('Only valid mehtods: ' . implode(', ', $validMethods));
			}

			/**
			 * And put through..
			 */
			return call_user_func_array([static::$interface, $methodName], $params);
			
		});
		
		/**
		 * Mmhh... It works?
		 */
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
		
		/**
		 * Api way to stop service.
		 */
		static::RegisterActionHandler('kill', function () {
			static::CheckMasterToken();
			die();
		});
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
	
	/**
	 * Began the magic.
	 */
	static public function Start(ServiceAuthorization $authorization): void {
		
		/**
		 *  It makes cancellabe by CTRL+C signal
		 */
		Thread::SetSignalHandler(SIGINT, function($signo) {
			die();
		});
		
		/**
		 * Boot the framework
		 */
		static::Boot($authorization->userName, $authorization->userAgent);
		
		/**
		 * Setup token for user service bearer
		 */
		static::SetToken($authorization->token);
		
		/**
		 * Setup secure token for master secure operations
		 */
		static::SetMasterToken($authorization->masterToken);
		
		/**
		 * Prepare action handlers
		 */
		static::RegisterActionHandlers();
		
		/**
		 * Run network service for auhtorized user
		 */
		static::Run($authorization->host, $authorization->port);
	}
}