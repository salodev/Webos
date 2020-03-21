<?php

namespace Webos\Service\NetworkService;

use Exception;
use salodev\Implementations\SimpleServer;

class Server extends SimpleServer {
	
	/**
	 *
	 * @var string 
	 */
	static protected $_token = null;
	
	/**
	 * @var ?array
	 */
	static protected $_lastRequest = null;
	
	/**
	 *
	 * @var array 
	 */
	static protected $_actionHandlers = [];
	
	static public function SetToken(string $token):void {
		self::$_token = $token;
	}
	
	static public function RegisterActionHandler(string $name, callable $handler): void {
		static::$_actionHandlers[$name] = $handler;
	}
	
	static public function GetActionsList(): array {
		return array_keys(static::$_actionHandlers);
	}
	
	/**
	 * 
	 * @param string $name
	 * @param string $token
	 * @param array $data
	 * @return string|array. Not is possible define a type now. It needs a refactor.
	 * @throws Exception
	 */
	static public function Call(string $name, string $token, array $data = [])  {
		if (static::$_token) {
			if (!$token) {
				throw new Exception('Missing token');
			}
			if (static::$_token != $token) {
				throw new Exception('Invalid token');
			}
		}
		if (!isset(static::$_actionHandlers[$name])) {
			throw new Exception("Undefined '{$name}' action handler");
		}
		$actionHandler = static::$_actionHandlers[$name];
		return $actionHandler($data);
	}
	
	static public function Run(string $address, int $port): void {
		
		parent::Listen($address, $port, function(string $reqString): string {
			$array = json_decode($reqString, true);
			if ($array==null) {
				return 'Bad json format: ' . $reqString;
			}
			
			static::$_lastRequest = $array;

			$command  = $array['command'];
			$data     = $array['data'   ] ?? [];
			$token    = $array['token'  ] ?? '';
			
			try {
				$commandResponse = static::Call($command, $token, $data);
			} catch(Exception $e) {
				static::LogException($e);
				return json_encode([
					'status' => 'error',
					'errorMsg' => $e->getMessage(),
				]);
			}

			static::LogDebug("enviando: " . print_r($commandResponse, true));
			return json_encode([
				'status' => 'ok',
				'data'   => $commandResponse,
			]);
		});
	}
	
	static public function GetLastRequest(): array {
		return static::$_lastRequest;
	}
}