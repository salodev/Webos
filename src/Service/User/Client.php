<?php
namespace Webos\Service\User;
use \salodev\ClientSocket as Socket;
use \Exception;

class Client {
	
	/**
	 *
	 * @var type \salodev\ClientSocket;
	 */
	private $_socket = null;
	
	private $_token = null;
	
	public function __construct(string $token, string $host = '127.0.0.1', int $port = 3000) {
		$this->_host = $host;
		$this->_port = $port;
		$this->_token = $token;
	}
	
	public function connect() {
		$this->_socket = new Socket("{$this->_host}:{$this->_port}");
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
		$this->_socket->write($msg."\n");
		$resp = $this->_socket->readAll(255);
		// echo "response: {$resp}\n";
		$json = json_decode($resp, true);
		if (!$json || !isset($json['status'])) {
			throw new Exception('Unexpected service response');
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
	
	public function renderAll() {
		return $this->call('renderAll');
	}
	
	public function action($data) {
		return $this->call('action', $data);
	}
}