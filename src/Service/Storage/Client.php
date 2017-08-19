<?php
namespace Webos\Service\Storage;
use salodev\ClientSocket;
use Exception;

class Client {
	static public $socket;
	static public function GetConnection():ClientSocket {
		if (self::$socket==null) {
			$socket = new ClientSocket('127.0.0.1:3001','rw');
			$socket->open('127.0.0.1:3001','rw');
			self::$socket = $socket;
		}
		return self::$socket;
	}

	static public function Store($name, $value) {
		$s = fsockopen('127.0.0.1', 3001);
		
		$message = $name .str_repeat(' ', 30) . $value . "\n\n";
		echo "enviando '{$message}'...\n";
		
		fwrite($s, $message, strlen($message));
	}
	
	static public function Read($name) {
		$s = fsockopen('127.0.0.1', 3001);
		
		fwrite($s, $name, strlen($name."\n"));
		return fread($s, 255);
		
		
		self::GetConnection()->write($name);
		$read = self::GetConnection()->readAll(255, PHP_BINARY_READ);
		return $read;
	}
}