<?php

namespace Webos;

use Webos\Implementations\Service\NetworkService\MasterServer;
use Webos\Implementations\Service\NetworkService\NetworkService;
use Webos\Implementations\Service\LocalService\LocalService;
use Webos\Implementations\Service\AuthService\AuthService;
use Webos\Implementations\Service\Service;
use Webos\Implementations\Web\Router;
use Webos\WorkSpaceHandlers\Session as SessionStorage;

class Webos {
	
	static public $development = false;
	
	static private $_serviceType           = 'local';
	static private $_applicationName       = '';
	static private $_applicationParams     = [];
	static private $_url                   = '';
	static private $_title                 = 'WebOS Application';
	static private $_favicon               = '';
	static private $_installationPath      = '';
	static private $_workSpacesPath        = '';	
	static private $_authApplicationName   = '';
	static private $_authApplicationParams = '';
	
	static public function SetupLocalService(string $workSpaceHandlerClassName = null): void {
		self::$_serviceType = 'local';
		if ($workSpaceHandlerClassName) {
			LocalService::SetWorkSpaceHandlerClassName($workSpaceHandlerClassName);
		}
	}
	
	static public function SetupNetworkService(string $host, int $port, string $userName = 'root'): void {
		static ::$_serviceType = 'network';
		MasterServer::SetHost($host);
		MasterServer::SetPort($port);
		MasterServer::SetUserName($userName);
	}

	static public function SetInstallationPath(string $path) {
		if (!is_dir($path)) {
			throw new \Exception('Invalid path for implementation installation');
		}
		self::$_installationPath = $path;
	}
	
	static public function GetInstallationPath(): string {
		return self::$_installationPath;
	}
	
	static public function SetWorkSpacesPath(string $path) {
		if (!is_dir($path)) {
			throw new \Exception('Invalid path for workspaces');
		}
		self::$_workSpacesPath = $path;
	}
	
	static public function GetWorkSpacesPath(): string {
		return self::$_workSpacesPath;
	}
	
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
	
	static public function GetApplicationName(): string {
		return self::$_applicationName;
	}
	
	static public function GetApplicationParams(): array {
		return self::$_applicationParams;
	}
	
	static public function Start(): void {
		
		if (empty($_SESSION['service'])) {
			if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? null)=='XMLHttpRequest') {
				Router::GetLogin(); // die here..
			}
			$service = new AuthService(self::GetAuthApplicationName(), self::GetAuthApplicationParams());
			$_SESSION['service'] = $service;
		} else {
			$service = $_SESSION['service'];
		}
		
		Router::route($service);

	}
	
	static public function CreateService(): Service {
		if (self::$_serviceType == 'local') {
			return new LocalService();
		} else {
			return new NetworkService();
		}
		return $service;
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
	
	// Authentication featues
	
	static public function SetAuthApplication(string $applicationName, array $applicationParams = []): void {
		self::$_authApplicationName = $applicationName;
		self::$_authApplicationParams = $applicationParams;
	}
	
	static public function GetAuthApplicationName(): string {
		return self::$_authApplicationName;
	}
	
	static public function GetAuthApplicationParams(): array {
		return self::$_authApplicationParams;
	}
	
	static public function Authorize(string $userName, array $authParams): bool {
		$service = static::CreateService();
		$service->auth($userName, $authParams);
		session_destroy();
		session_start();
		SessionStorage::$stopStore = true;
		$_SESSION['service'] = $service;
		return true;
	}
	
	static public function Authenticate(string $userName, array $authParams) {
		$authAppClassName = self::GetAuthApplicationName();
		$authAppClassName::Authenticate($userName, $authParams);
		$_SESSION['userName'] = $userName;
	}
	
	static public function GetSourceRootPath(): string {
		return dirname(__DIR__);
	}

}