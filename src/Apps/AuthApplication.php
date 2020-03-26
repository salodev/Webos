<?php

namespace Webos\Apps;

use Webos\Webos;
use Webos\Application;

abstract class AuthApplication extends Application {
	
	final static public function Login(string $userName, array $authParams = []) {
		$service = \Webos\Webos::Authorize($userName, $authParams);
	}

	abstract static public function Authenticate(string $userName, array $authParams): void;

}