<?php

namespace Webos\Service\Server;

use Exception;
use salodev\Implementations\SimpleServer;

class Base {
	
	/**
	 *
	 * @var string 
	 */
	static private $_token = null;
	
	/**
	 *
	 * @var array 
	 */
	static private $_actionHandlers = [];
	
	static public function SetToken(string $token):void {
		self::$_token = $token;
	}
	
	static public function RegisterActionHandler($name, $handler) {
		self::$_actionHandlers[$name] = $handler;
	}
	
	static public function GetActionsList(): array {
		return array_keys(self::$_actionHandlers);
	}
	
	static public function Call($name, $token, array $data = [])  {
		if (self::$_token) {
			if (!$token) {
				throw new Exception('Missing token');
			}
			if (self::$_token != $token) {
				throw new Exception('Invalid token');
			}
		}
		if (!isset(self::$_actionHandlers[$name])) {
			throw new Exception("Undefined '{$name}' action handler");
		}
		$actionHandler = self::$_actionHandlers[$name];
		return $actionHandler($data);
	}
	
	static public function Listen($address, $port) {
		
		SimpleServer::Listen($address, $port, function($reqString) {
			$json = json_decode($reqString, true);
			if ($json==null) {
				return 'Bad json format: ' . $reqString;
			}

			$command  = $json['command'];
			$data     = $json['data'   ] ?? [];
			$token    = $json['token'  ] ?? null;
			
			try {
				$commandResponse = self::Call($command, $token, $data);
			} catch(Exception $e) {
				echo "Command Exception: {$e->getMessage()} at file '{$e->getFile()}' ({$e->getLine()})\n\n";
				echo $e->getTraceAsString();
				return json_encode([
					'status' => 'error',
					'errorMsg' => $e->getMessage(),
				]);
			}

			// echo "enviando: " . print_r($commandResponse, true);
			return json_encode([
				'status' => 'ok',
				'data'   => $commandResponse,
			]);
		});
	}
}