<?php

namespace Webos\Implementations\Web;

use Webos\Webos;
use Webos\Stream\Content as StreamContent;
use Webos\Implementations\Service\Service;

class Router {	
	
	static public function route(Service $service) {
		$protocol = $_SERVER['REQUEST_SCHEME'] ?? 'http';
		$fullUrl  = "{$protocol}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
		$urlParts = parse_url($fullUrl);
		$path     = $urlParts['path'];
		$basePath = (parse_url(Webos::GetUrl())['path']) ?? '';
		$uri      = substr($path, strlen($basePath));

		$route = array_values(array_filter(explode('/', $uri)))[0]??null;
		
		if (empty($route)) {
			$route = 'renderAll';
		}
		
		$routeMethod = "route_{$route}";
		if (!method_exists(static::class, $routeMethod)) {
			die("Unexistent route {$route}");
		}
		try {
			$return = static::$routeMethod($service);
		} catch (\Exception $e) {
			static::SendError($e);
		}
		if (is_array($return)) {
			static::SendJson($return);
		}
		if ($return instanceof StreamContent) {
			$return->streamIt();
		}
	}
	
	static public function route_action(Service $service) {
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
	
	static public function route_syncViewportSize(Service $service) {
		if (empty($_REQUEST['width']) || empty($_REQUEST['height'])) {
			return [
				'status'  => 'error',
				'message' => 'Missing width or height'
			];
		}
		$service->setViewportSize((int)$_REQUEST['width'], (int)$_REQUEST['height']);
		return [];
	}
	
	static public function route_getOutputStream(Service $service) {
		$data = $service->getOutputStream();
		return StreamContent::CreateFromArray($data);
		
	}
	
	static public function route_getMediaContent(Service $service) {
		$objectID     = $_REQUEST['objectID'  ];
		$params       = $_REQUEST['params'    ] ?? [];
		$data = $service->getMediaContent($objectID, $params);
		return StreamContent::CreateFromArray($data);
	}
	
	static public function route_renderAll(Service $service) {
		ob_start('ob_gzhandler');
		echo $service->renderAll();
		die();
	}
	
	static public function route_debug(Service $service) {
		$service->debug();
		die();
	}
	
	static public function route_img(Service $service) {
		static::_route_resource();
	}
	
	static public function route_js(Service $service) {
		static::_route_resource();
	}
	
	static public function route_css(Service $service) {
		static::_route_resource();
	}
	
	static public function route_fonts(Service $service) {
		static::_route_resource($service);
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
		ob_end_clean();
		ob_start('ob_gzhandler');
		header('Content-Type: text/json');
		die(json_encode($json));
	}
	
	static public function SendError($e) {
		static::SendJson([
			'error' => $e->getMessage(),
			'trace' => $e->getTraceAsString(),
		]);
	}
}
