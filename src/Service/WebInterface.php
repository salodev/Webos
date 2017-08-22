<?php

namespace Webos\Service;

class WebInterface {
	static public $dev = false;
	static public function GetUserInterface(string $user, string $applicationName): UserInterface {
		if (self::$dev) {
			return new DevInterface($user, $applicationName);
		} else {
			return new ProductionInterface($user, $applicationName);
		}
	}
}