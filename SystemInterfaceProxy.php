<?php
namespace Webos;
class SystemInterfaceProxy {
	static private $interface = null;
	static public function getInterface() {
		if (!self::$interface) {
			self::$interface = new SystemInterface();
		}

		return self::$interface;
	}
}