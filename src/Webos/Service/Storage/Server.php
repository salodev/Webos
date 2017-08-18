<?php
namespace Webos\Service\Storage;
use salodev\Socket;
use Exception;

class Server {
	static private $_storage = [];
	static public $socket = null;
	static public function Listen($address, $port) {
		$socket = new Socket();
		self::$socket = $socket;
		$socket->create(AF_INET, SOCK_STREAM, SOL_TCP);
		$socket->setBlocking();
		if ($socket->bind($address, $port)===false) {
			echo "error en bind()...\n";
		}
		$socket->listen();
		do {
			$connection = $socket->accept();
			if (!($connection instanceof Socket)) {
				echo "error al accept()...\n";
				break;
			}
			$connection->setBlocking();
			$message = trim($connection->readAll(256, PHP_NORMAL_READ));
			echo "mensaje: '{$message}'";
			$key = trim(substr($message, 0, 30));
			$value   = substr($message, 30);
			// $connection->write("Action: '{$action}', Data: '{$data}'");
			if (!strlen($value)) {
				echo "leyendo key '{$key}'\n";
				$value = self::$_storage[$key] ?? '';
				echo "enviando {$value}\n";
				$connection->write($value);
			} else {
				self::$_storage[$key] = $value;
			}
			print_r(self::$_storage);
			$connection->close();
		} while(true);
		$socket->close();
	}
}