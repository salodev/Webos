<?php

namespace Webos\Implementations;
use Webos\Apps\Auth;
use Exception;

class Authentication {
	static private $_applicationName   = Auth::class;
	static private $_applicationParams = [];
	
	static private $_loginFn;
	static private $_registerFn;
	
	static public function SetLoginFn(callable $fn): void {
		self::$_loginFn = $fn;
	}
	
	static public function Login($username, $password): bool {
		if (!is_callable(self::$_loginFn)) {
			throw new Exception('No login FN registered');
		}
		$fn = self::$_loginFn;
		return $fn($username, $password);
	}
	
	static public function SetRegisterFn(callable $fn): void {
		self::$_registerFn = $fn;
	}
	
	static public function Register(array $params = []): bool {
		if (!is_callable(self::$_registerFn)) {
			throw new Exception('No register FN registered');
		}
		$fn = self::$_registerFn;
		return $fn($params);
	}
	
	static public function SetApplicationName(string $applicationName): void {
		self::$_applicationName = $applicationName;
	}
	
	static public function GetApplicationName(): string {
		return self::$_applicationName;
	}
	
	static public function SetApplicationParams(array $applicationParams): void {
		self::$_applicationParams = $applicationParams;
	}
	
	static public function GetApplicationParams(): array {
		return self::$_applicationParams;
	}
}