<?php

namespace Webos\Service;
use Webos\SystemInterface;
use Webos\WorkSpaceHandlers\FileSystem AS FileSystemHanlder;

class ProductionInterface implements UserInterface {
	
	private $_client = null;
	
	public function __construct(string $userName, string $applicationName) {
		$userName = $_SESSION['username' ] ?? $userName;
		$port     = $_SESSION['port' ] ?? null;
		$token    = $_SESSION['token'] ?? null;
		
		if (!$userName || !$port || !$token) {
			$client = new Client('root', '127.0.0.1', 3000);
			$ret = $client->call('create', [
				'userName'        => $userName,
				'applicationName' => $applicationName,
			]);
			$_SESSION['port' ] = $ret['port'];
			$_SESSION['token'] = $ret['token'];
		}
		$this->_client = new Client($_SESSION['token'], '127.0.0.1', $_SESSION['port']);
	}
	
	public function renderAll(): string {
		return $this->_client->call('renderAll');
	}
	
	public function action(string $name, string $objectID, array $parameters, bool $ignoreUpdateObject = false): array {
		return $this->_client->call('action', [
			'name'       => $name,
			'objectID'   => $objectID,
			'parameters' => $parameters,
			'ignoreUpdateObject' => $ignoreUpdateObject,
		]);
	}
}