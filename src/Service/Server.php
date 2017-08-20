<?php

namespace Webos\Service;

use Exception;
use salodev\Implementations\SimpleServer;
use salodev\Debug\ObjectInspector;
use Webos\WorkSpace;
use Webos\SystemInterface;
use Webos\WorkSpaceHandlers\Instance as InstanceHandler;
use Webos\FrontEnd\Page;

class Server {
	
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
	
	static public function RegisterActionHandler($name, $handler) {
		self::$_actionHandlers[$name] = $handler;
	}
	
	static public function GetWorkSpace(): WorkSpace {
		return self::$system->getWorkSpace();
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
		
		self::RegisterActionHandler('setToken', function(array $data) {
			if (self::$_token) {
				throw new Exception('Token cant be modified');
			}
			if (empty($data['token'])) {
				throw new Exception('Missing token data');
			}
			self::$_token = $data['token'];
		});
		
		SimpleServer::Listen($address, $port, function($reqString) {
			$json = json_decode($reqString, true);
			if ($json==null) {
				return 'Bad json format: ' . $reqString;
			}

			$command  = $json['command'];
			$data     = $json['data'];
			$token    = $json['token'] ?? null;
			
			try {
				$commandResponse = self::Call($command, $token, $data);
			} catch(Exception $e) {
				echo "Command Exception: {$e->getMessage()} at file '{$e->getFile()}' ({$e->getLine()})\n\n";
				\Webos\Log::write("USER SERVICE. Command Exception: {$e->getMessage()} at file '{$e->getFile()}' ({$e->getLine()})\n\n");
				echo $e->getTraceAsString();
				return json_encode(array(
					'status' => 'error',
					'errorMsg' => $e->getMessage(),
				));
			}

			// echo "enviando: " . print_r($commandResponse, true);
			return json_encode(array(
				'status' => 'ok',
				'data'   => $commandResponse,
			));
		});
	}
}