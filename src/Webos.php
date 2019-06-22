<?php
namespace Webos;
class Webos {
	
	static public $development = false;
	
	static public function GetInstallationPath(): string {
		return dirname(dirname(__FILE__));
	}
}