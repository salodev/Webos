<?php
namespace Webos\Service;
use Exception;
use salodev\ClientSocket as Socket;
use Webos\Log;

class Client {
	
	/**
	 *
	 * @var type \salodev\ClientSocket;
	 */
	private $_socket = null;
	
	private $_token = null;
	private $_host = null;
	private $_port = null;
	
	public function __construct(string $token, string $host = '127.0.0.1', int $port = 3000) {
		$this->_host = $host;
		$this->_port = $port;
		$this->_token = $token;
	}
	
	public function getPort() {
		return $this->_port;
	}
	
	public function connect() {
		$this->_socket = new Socket("{$this->_host}:{$this->_port}");
	}
	
	public function waitForService($maxTime = 5) {
		$start = time();
		$socket = null;
		do {
			try {
				$socket = new Socket("{$this->_host}:{$this->_port}");
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
	
	public function call( string $commandName, array $data = array()) {
		$this->connect();
		$this->_socket->setBlocking();
		$msg = json_encode(array(
			'command'  => $commandName,
			'data'     => $data,
			'token'    => $this->_token,
		));
		// $resp = $this->_socket->writeAndRead();
		
		Log::write(" *** ENVIANDO: $msg\n");
		
		$this->_socket->write($msg."\n");
		$resp = $this->_socket->readAll(255);
		
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
}