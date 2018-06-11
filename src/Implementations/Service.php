<?php

namespace Webos\Implementations;
use Webos\Service\DevService;
use Webos\Service\ProductionService;
use Webos\Service\AuthService;
use Webos\Service\UserService;
use Webos\Implementations\Authentication;
use Webos\Apps\Auth as AuthApplication;

class Service {
	static public $dev = true;
	static private $_applicationName = '';
	static private $_applicationParams = [];
	
	static public function SetApplication(string $className, array $params = []): void {
		self::$_applicationName   = $className;
		self::$_applicationParams = $params;
	}
	
	static public function Create(string $user, string $applicationName, array $applicationParams = []): UserService {
		if (self::$dev) {
			return new DevService($user, $applicationName, $applicationParams);
		} else {
			return new ProductionService($user, $applicationName, $applicationParams);
		}
	}
	
	static public function CreateAuth(): UserService {
		return new AuthService('', Authentication::GetApplicationName(), Authentication::GetApplicationParams());
	}
	
	static public function Start(): void {
		if (empty($_SESSION['username'])) {
			if (empty($_SESSION['ws'])) {
				if (($_SERVER['HTTP_X_REQUESTED_WITH']??null)=='XMLHttpRequest') {
					self::GetLogin();
				}			
			}
			// self::GetLogin($location);
			$service = self::CreateAuth();
		} else {
			$userName = $_SESSION['username'];

			$service = self::Create($userName, self::$_applicationName, self::$_applicationParams);
		}
		
		if (!empty($_REQUEST['debug'])) {
			self::Debug($service);
		}
		
		if(!empty($_REQUEST['actionName'])) {
			self::DoAction($service);
			return;
		} else {
			self::RenderAll($service);
		}
	}
	
	static public function DoAction(UserService $service): void {
		$actionName   = $_REQUEST['actionName'];
		$objectID     = $_REQUEST['objectID'  ];
		$params       = $_REQUEST['params'    ] ?? [];
		$ignoreUpdate = $params['ignoreUpdateObject'] ?? false;
		
		$response = $service->action($actionName, $objectID, $params, $ignoreUpdate);
		
		self::SendJson($response);
	}
	
	static public function RenderAll(UserService $service): void {
		echo $service->renderAll();
		die();
	}
	
	static public function Debug(UserService $service): void {
		$service->debug();
		die();
	}
	
	static public function GetLogin(): void {
		self::SendJson(['events'=>[['name'=>'authUser']]]);
	}
	
	static public function SendJson($json): void {
		header('Content-Type: text/json');
		die(json_encode($json));
	}
}