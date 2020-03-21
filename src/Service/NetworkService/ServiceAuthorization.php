<?php

namespace Webos\Service\NetworkService;

use salodev\Pcntl\Child;
use salodev\Pcntl\Thread;

class ServiceAuthorization {
	
	public $userName;
	public $port;
	public $host;
	public $token;
	public $masterToken;
	public $applicationName;
	public $applicationParams = [];
	public $loadStoredWorkSpace = false;
	public $userAgent = null;
	public $created = null;
	
	/**
	 *
	 * @var Child 
	 */
	private $childProcess = null;
	
	public function setChildProcess(Child $childProcess): self {
		$this->childProcess = $childProcess;
		return $this;
	}
	
	public function getChildProcess(): Child {
		$child = $this->childProcess;
		if (!Thread::HasChild($child)) {
			throw new \Exception('You can\'t access to child process por this user service');
		}
		return $child;
	}
	
	public function getClient(): Client {
		$client = new Client($this->token, $this->host, $this->port);
		if ($this->masterToken) {
			$client->setMasterToken($this->masterToken);
		}
		return $client;
	}
}
