<?php

namespace Webos\Service\NetworkService;

use Webos\Service\Service;
use Exception;

class NetworkService extends Service {
	
	private $_client = null;
	private $_userName = null;
	
	public function __construct(string $userName, string $applicationName, array $applicationParams = [], array $metadata = []) {
		
		$this->_userName          = $userName;
		$this->_applicationName   = $applicationName;
		$this->_applicationParams = $applicationParams;
		$this->_metadata          = $metadata;
		
		$userName = $_SESSION['username' ] ?? $userName;
		$port     = $_SESSION['port'     ] ?? null;
		$token    = $_SESSION['token'    ] ?? null;
		
		if (!$userName || !$port || !$token) {
			$this->_getService();
		}
		$this->_client = $this->_createClient($_SESSION['token'], '127.0.0.1', $_SESSION['port']);
	}
	
	private function _getService() {
		$client = $this->_createClient('root', '127.0.0.1', 3000);
		$ret = $client->call('create', [
			'userName'          => $this->_userName,
			'applicationName'   => $this->_applicationName,
			'applicationParams' => $this->_applicationParams,
			'userAgent'         => $_SERVER['HTTP_USER_AGENT'] || '',
		]);
		$_SESSION['port' ] = $ret['port' ];
		$_SESSION['token'] = $ret['token'];
	}
	
	public function renderAll(): string {
		return $this->_remote('renderAll');
	}
	
	public function action(string $name, string $objectID, array $parameters, bool $ignoreUpdateObject = false): array {
		return $this->_remote('action', [$name, $objectID, $parameters, $ignoreUpdateObject]);
	}
	
	public function getOutputStream(): array {
		return $this->_remote('getOuputStream');
	}
	
	public function getFilestoreDirectory(): string {
		return $this->_remote('getFilestoreDirectory');
	}
	
	public function getMediaContent(string $objectID, array $params = []): array {
		return $this->_remote('getMediaContent', [$objectID, $params]);
	}
	
	public function setViewportSize(int $width, int $height): void {
		$this->_remote('setViewportSize', [$width, $height]);
	}
	
	public function debug():void {
		$html = $this->_remote('debug', [$_REQUEST['__path'] ?? null]);
		echo $html; die();
	}
	
	private function _createClient($token, $host, $port): Client {
		$client = new Client($token, $host, $port);
		$client->setLogHandler(function($message) use ($client) {
			$to = "{$client->getHost()}:{$client->getPort()}";
			// file_put_contents('/var/www/sg/private/log/client.log', "[{$to}] {$message}\n", FILE_APPEND);
		});
		
		return $client;
	}
	
	private function _call(string $actionName, array $params = []) {
		try {
			return $this->_client->call($actionName, $params);
		} catch (Exception $e){
			$this->_getService();
			$this->_client = $this->_createClient($_SESSION['token'], '127.0.0.1', $_SESSION['port']);
			return $this->_client->call($actionName, $params);
		}
	}
	
	private function _remote($methodName, array $params = []) {
		return $this->_call('interface', [$methodName, $params]);
	}

}