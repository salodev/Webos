<?php

namespace Webos\Service\Server\ConsoleTool;

use salodev\IO\Cli;

class ConsoleTool {
	
	static public function Run() {
		$commandName = Cli::getRawParamsArray(0);
		if ($commandName == 'list') {
			die('esto es un listado');
		}
	}
}
