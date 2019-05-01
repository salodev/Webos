<?php

namespace Webos\Implementations;
use Webos\Service\DevService;
use Webos\Service\ProductionService;
use Webos\Service\AuthService;
use Webos\Service\UserService;
use Webos\Implementations\Authentication;
use Webos\Apps\Auth as AuthApplication;
use Webos\Stream\Content as StreamContent;

class Service {
	static public $dev = true;
	static private $_applicationName = '';
	static private $_applicationParams = [];
	static private $_url = '';
	
	static public function SetUrl(string $url): void {
		self::$_url = $url;
	}
	
	static public function GetUrl(): string {
		return self::$_url;
	}
	
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
		$uri = $_SERVER['REQUEST_URI'];
		
		if (preg_match('/\/(img|js|css|fonts)\/.*/', $uri, $matches)) {
			ResourcesLoader::ServeFile($matches[1], $matches[0]);
		}
		
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
		} elseif (!empty($_REQUEST['getOutputStream'])) {
			self::GetOutputStream($service);
			return;
		} elseif (!empty($_REQUEST['getMediaContent'])) {
			self::GetMediaContent($service);
		} else {
			self::RenderAll($service);
		}
	}
	
	static public function DoAction(UserService $service): void {
		$actionName   = $_REQUEST['actionName'];
		$objectID     = $_REQUEST['objectID'  ];
		$params       = $_REQUEST['params'    ] ?? [];
		$ignoreUpdate = $params['ignoreUpdateObject'] ?? false;
		
		if (!empty($_FILES) && !empty($_FILES['file'])) {
			$params['__uploadedFile'] = $_FILES['file']['tmp_name'];
		}
		
		$response = $service->action($actionName, $objectID, $params, $ignoreUpdate);

		
		//print_r($response); die();
		if (!empty($response['events'])) {
			foreach($response['events'] as $event) {
				if ($event['name']=='authUser' && !($service instanceof AuthService)) {
					session_destroy();
				}
			}
		}
		
		self::SendJson($response);
	}
	
	static public function GetOutputStream(UserService $service): void {
		$data = $service->getOutputStream();
		$output = StreamContent::CreateFromArray($data);
		$output->streamIt();
		
	}
	
	static public function GetMediaContent(UserService $service):void {
		$objectID     = $_REQUEST['objectID'  ];
		$params       = $_REQUEST['params'    ] ?? [];
		$data = $service->getMediaContent($objectID, $params);
		$output = StreamContent::CreateFromArray($data);
		$output->streamIt();
	}
	
	static public function RenderAll(UserService $service): void {
		ob_start('ob_gzhandler');
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
		ob_start('ob_gzhandler');
		header('Content-Type: text/json');
		die(json_encode($json));
	}
}