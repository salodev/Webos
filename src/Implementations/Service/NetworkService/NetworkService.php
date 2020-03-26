<?php

namespace Webos\Implementations\Service\NetworkService;

use Webos\Implementations\Service\Service;
use Exception;

/**
 * NetworkService adapter for user service
 * It stores auth info and connect to user service server
 */
class NetworkService implements Service {
	
	public $userName   = null;
	public $host       = null;
	public $port       = null;
	public $token      = null;
	public $authParams = [];
	
	public function auth(string $userName, array $authParams): void {
		
		/**
		 * We need a client for master service, so use a factory method
		 */
		$client = $this->_createMasterClient();
		
		/**
		 * Request a dedicated service for current user.
		 * Server will check authParams and return service information for 
		 * connect if valid or will throw an error.
		 */
		$ret = $client->call('create', [
			'userName'          => $userName,
			'authParams'        => $authParams,
			'userAgent'         => $_SERVER['HTTP_USER_AGENT'] || '',
		]);
		
		/**
		 * Because this adapter instace will be stored in web user session,
		 * we can keep user service connection information stored here.
		 */
		$this->userName   = $userName;
		$this->host       = $ret['host' ];
		$this->port       = $ret['port' ];
		$this->token      = $ret['token'];
		$this->authParams = $authParams;
	}
	
	public function renderAll(): string {
		return $this->_remote('renderAll');
	}
	
	public function action(string $name, string $objectID, array $parameters, bool $ignoreUpdateObject = false): array {
		return $this->_remote('action', [$name, $objectID, $parameters, $ignoreUpdateObject]);
	}
	
	public function getOutputStream(): array {
		return $this->_remote('getOutputStream');
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
	
	public function debug(): void {
		$html = $this->_remote('debug', [$_REQUEST['__path'] ?? null]);
		echo $html; die();
	}
	
	/**
	 * Create a custom ready client
	 */
	private function _createClient(string $token, string $host, int$port): Client {
		$client = new Client($token, $host, $port);
		$client->setLogHandler(function($message) use ($client) {
			$to = "{$client->getHost()}:{$client->getPort()}";
			file_put_contents('/var/www/sg/private/log/client.log', "[{$to}] {$message}\n", FILE_APPEND);
		});
		
		return $client;
	}

	/**
	 * Master Service client factory method.
	 * Because take initial configuration is required, once we need it we
	 * just want se the creation but not how.
	 * 
	 */
	private function _createMasterClient(): Client {
		return $this->_createClient(MasterServer::GetUserName(), MasterServer::GetHost(), MasterServer::GetPort());
	}
	
	/**
	 * User Service client factory Method.
	 * It care use stored connection information and retrieve ready client.
	 */
	private function _getUserClient(): Client {
		return $this->_createClient($this->token, $this->host, $this->port);
	}
	
	/**
	 * Get User service client and make a call. 
	 * Any server return will passed through
	 */
	private function _call(string $actionName, array $params = []) {
		try {
			return $this->_getUserClient()->call($actionName, $params);
		} catch (Exception $e){
			$this->auth($this->userName, $this->authParams);
			return $this->_getUserClient()->call($actionName, $params);
		}
	}
	
	/**
	 * Wrapper for remote SystemInterface method call.
	 */
	private function _remote($methodName, array $params = []) {
		return $this->_call('interface', [$methodName, $params]);
	}

}