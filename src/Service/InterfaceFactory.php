<?php

namespace Webos\Service;

class InterfaceFactory {
	static public $dev = true;
	static public function Create(string $user, string $applicationName): UserInterface {
		if (self::$dev) {
			return new DevInterface($user, $applicationName);
		} else {
			return new ProductionInterface($user, $applicationName);
		}
	}
}