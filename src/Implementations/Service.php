<?php

namespace Webos\Implementations;

use Webos\Service\NetworkService\NetworkService;
use Webos\Service\LocalService\LocalService;
use Webos\Service\AuthService\AuthService;
use Webos\Service\Service as SystemService;
use Webos\Implementations\Authentication;
use Webos\Stream\Content as StreamContent;

class Service {
	static public $dev = true;
	static private $_applicationName = '';
	static private $_applicationParams = [];
	static private $_url = '';
	static private $_title = 'WebOS Application';
	static private $_favicon = '';
	
	static public function SetTitle(string $title): void {
		self::$_title = $title;
	}
	
	static public function GetTitle(): string {
		return self::$_title;
	}
	
	static public function SetFavicon(string $favicon): void {
		self::$_favicon = $favicon;
	}
	
	static public function GetFavicon(): string {
		return self::$_favicon;
	}
	
	static public function SetUrl(string $url): void {
		self::$_url = $url;
	}
	
	static public function GetUrl(): string {
		if (!self::$_url) {
			self::$_url = self::AutoGenerateUrl();
		}
		return self::$_url;
	}
	
	static public function SetApplication(string $className, array $params = []): void {
		self::$_applicationName   = $className;
		self::$_applicationParams = $params;
	}
	
	static public function Start(): void {
		/*set_exception_handler(function($e) {
			if (static::$dev == true) {
				echo '<pre>';
				echo \salodev\Debug\ExceptionDumper::DumpFromThrowable($e);
				echo '</pre>';
			} else {
				echo 'An unexpected error was ocurred..';
				die();
			}
		});*/
		
		static::route();

	}
	
	static public function CreateService(): SystemService {
		if (empty($_SESSION['username'])) {
			if (empty($_SESSION['ws'])) {
				if (($_SERVER['HTTP_X_REQUESTED_WITH']??null)=='XMLHttpRequest') {
					self::GetLogin();
					die();
				}			
			}			
			// self::GetLogin($location);
			return new AuthService('', Authentication::GetApplicationName(), Authentication::GetApplicationParams());
		} else {
			$userName = $_SESSION['username'];

			if (self::$dev) {
				return new LocalService($userName, self::$_applicationName, self::$_applicationParams);
			} else {
				return new NetworkService($userName, self::$_applicationName, self::$_applicationParams);
			}
		}
		return $service;
	}
	
	static public function route() {
		$fullUrl = "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
		$urlParts = parse_url($fullUrl);
		$path = $urlParts['path'];
		
		$uri = substr($path, strlen(parse_url(static::GetUrl())['path']));
		$route = explode('/', $uri)[0];
		
		if (empty($route)) {
			$route = 'renderAll';
		}
		
		$routeMethod = "route_{$route}";
		if (!method_exists(static::class, $routeMethod)) {
			die("Unexistent route {$route}");
		}
		$return = static::$routeMethod();
		if (is_array($return)) {
			static::SendJson($return);
		}
		if ($return instanceof StreamContent) {
			$return->streamIt();
		}
	}
	
	static public function route_action() {
		$service = static::CreateService();
		$actionName   = $_REQUEST['actionName'];
		$objectID     = $_REQUEST['objectID'  ];
		$params       = $_REQUEST['params'    ] ?? [];
		$ignoreUpdate = $params['ignoreUpdateObject'] ?? false;
		
		//print_r($_REQUEST);die();
		if (!empty($_FILES) && !empty($_FILES['file'])) {
			$params['__uploadedFile'] = $_FILES['file']['tmp_name'];
		}
		
		$response = $service->action($actionName, $objectID, $params, $ignoreUpdate);

		if (!empty($response['events'])) {
			foreach($response['events'] as $event) {
				if ($event['name']=='authUser' && !($service instanceof AuthService)) {
					session_destroy();
				}
			}
		}
		
		return $response;
	}
	
	static public function route_syncViewportSize() {
		$service = static::CreateService();
		if (empty($_REQUEST['width']) || empty($_REQUEST['height'])) {
			return [
				'status'  => 'error',
				'message' => 'Missing width or height'
			];
		}
		$service->setViewportSize((int)$_REQUEST['width'], (int)$_REQUEST['height']);
		return [];
	}
	
	static public function route_getOutputStream() {
		$service = static::CreateService();
		$data = $service->getOutputStream();
		return StreamContent::CreateFromArray($data);
		
	}
	
	static public function route_getMediaContent() {
		$service = static::CreateService();
		$objectID     = $_REQUEST['objectID'  ];
		$params       = $_REQUEST['params'    ] ?? [];
		$data = $service->getMediaContent($objectID, $params);
		return StreamContent::CreateFromArray($data);
	}
	
	static public function route_renderAll() {
		$service = static::CreateService();
		ob_start('ob_gzhandler');
		echo $service->renderAll();
		die();
	}
	
	static public function route_debug() {
		$service = static::CreateService();
		$service->debug();
		die();
	}
	
	static public function route_img() {
		static::_route_resource($service);
	}
	
	static public function route_js() {
		static::_route_resource();
	}
	
	static public function route_css() {
		static::_route_resource();
	}
	
	static public function route_fonts() {
		static::_route_resource();
	}
	
	static protected function _route_resource() {
		if (!preg_match('/\/(img|js|css|fonts)\/.*/', $_SERVER['REQUEST_URI'], $matches)) {
			die('Invalid url');
		}
		ResourcesLoader::ServeFile($matches[1], $matches[0]);
	}
	
	static public function GetLogin(): void {
		self::SendJson(['events'=>[['name'=>'authUser']]]);
	}
	
	static public function SendJson($json): void {
		ob_start('ob_gzhandler');
		header('Content-Type: text/json');
		die(json_encode($json));
	}
	
	static public function AutoGenerateUrl() {
		$protocols = [
			'HTTP/1.0' => 'http',
			'HTTP/1.1' => 'http',
		];
		$urlProtocol = $protocols[$_SERVER['SERVER_PROTOCOL']]??'http';
		$urlHost     = $_SERVER['HTTP_HOST'];
		return "{$urlProtocol}://{$urlHost}/";
	}
}