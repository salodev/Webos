<?php
namespace Webos\Service;
use Exception;
use salodev\IO\ClientSocket as Socket;

class Client {
	
	/**
	 *
	 * @var type \salodev\ClientSocket;
	 */
	private $_socket = null;
	
	private $_token = null;
	private $_host = null;
	private $_port = null;
	
	private $_logHandler = null;
	
	public function __construct(string $token = null, string $host = '127.0.0.1', int $port = 3000) {
		$this->_host = $host;
		$this->_port = $port;
		$this->_token = $token;
	}
	
	public function getPort(): int {
		return $this->_port;
	}
	
	public function connect(): void {
		$this->_socket = Socket::Create($this->_host, $this->_port);
	}
	
	public function checkAvailable(): bool {
		$socket = Socket::Create($this->_host, $this->_port, 0.5);
		$socket->close();
		return true;
	}
	
	public function waitForService(int $maxTime = 5): bool {
		$start = time();
		$socket = null;
		do {
			try {
				$socket = Socket::Create($this->_host, $this->_port);
			} catch (Exception $e) {
				usleep(1000);
			}
			if(time()-$start>$maxTime) {
				break;
			}
		} while(!($socket instanceof Socket));
		
		if ($socket instanceof Socket) {
			$socket->close();
			return true;
		}
		return false;
	}
	
	public function call( string $commandName, array $data = []) {
		$this->connect();
		$this->_socket->setBlocking();
		
		$logHandler = $this->_logHandler ?? function() {};
		
		$msg = json_encode(array(
			'command'  => $commandName,
			'data'     => $data,
			'token'    => $this->_token,
		));
		
		$logHandler('SEND ' . $msg);
		
		$resp = $this->_socket->writeAndRead($msg);
		
		$logHandler('RECV ' . $resp);
		
		$json = json_decode($resp, true);
		if (!$json || !isset($json['status'])) {
			throw new Exception('Unexpected service response: ' . $resp);
		}
		if ($json['status'] != 'ok') {
			if (!isset($json['errorMsg'])) {
				throw new Exception('Unexpected service response: missing errorMsg');
			}
			throw new Exception($json['errorMsg']);
		}
		if (!isset($json['data'])) {
			// throw new Exception('Unexpected service response: missing data');
		}
		
		return $json['data'] ?? null;
	}
	
	public function setLogHandler(callable $fn) {
		$this->_logHandler = $fn;
	}
}