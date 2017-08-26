<?php

namespace Webos\Service;

class Implementation {
	static public $dev = true;
	static public function CreateInterface(string $user, string $applicationName): UserInterface {
		if (self::$dev) {
			return new DevInterface($user, $applicationName);
		} else {
			return new ProductionInterface($user, $applicationName);
		}
	}
	
	static public function Start(string $applicationName, string $location = null, $debug = false) {
		if (empty($_SESSION['username'])) {
			self::GetLogin($location);
		}
		$userName = $_SESSION['username'];
		
		$interface = self::CreateInterface($userName, $applicationName);
		
		if ($debug) {
			self::Debug($interface);
		}
		
		if(!empty($_REQUEST['actionName'])) {
			self::DoAction($interface);
			return;
		} else {
			self::RenderAll($interface);
		}
	}
	
	static public function DoAction(UserInterface $interface) {
		$actionName   = $_REQUEST['actionName'];
		$objectID     = $_REQUEST['objectID'  ];
		$params       = $_REQUEST['params'    ] ?? [];
		$ignoreUpdate = $params['ignoreUpdateObject'] ?? false;
		
		$response = $interface->action($actionName, $objectID, $params, $ignoreUpdate);
		
		self::SendJson($response);
	}
	
	static public function RenderAll(UserInterface $interface) {
		echo $interface->renderAll();
		die();
	}
	
	static public function Debug(UserInterface $interface) {
		$interface->debug();
		die();
	}
	
	static public function GetLogin(string $location = null) {
		
		if (($_SERVER['HTTP_X_REQUESTED_WITH']??null)=='XMLHttpRequest') {
			self::SendJson(['events'=>[['name'=>'authUser']]]);
		}
		
		header('location: ' . $location ?? '/');
		die();
	}
	
	static public function SendJson($json) {
		header('Content-Type: text/json');
		die(json_encode($json));
	}
}