<?php
namespace Webos;
use \salodev\ClientSocket as Socket;

class Client {
	private $_socket = null;
	private $_username = null;
	public function __construct($username, $host = '127.0.0.1', $port = 3000) {
		$this->_username = $username;
		$this->_socket = new Socket("{$host}:{$port}");
	}
	public function call($commandName, array $data = array()) {
		$resp = $this->_socket->writeAndRead(json_encode(array(
			'username' => $this->_username,
			'command'  => $commandName,
			'data'     => $data,
		)));
		$json = json_decode($resp, true);
		if (!$json || !isset($json['status'])) {
			throw new \Exception('Unexpected service response');
		}
		if ($json['status'] != 'ok') {
			if (!isset($json['errorMsg'])) {
				throw new \Exception('Unexpected service response: missing errorMsg');
			}
			throw new \Exception($json['errorMsg']);
		}
		if (!isset($json['data'])) {
			throw new \Exception('Unexpected service response: missing data');
		}
		
		return $json['data'];
	}
	public function renderAll() {
		return $this->call('renderAll');
	}
	public function action($actionName, $data) {
		return $this->call('action', $data);
	}
}