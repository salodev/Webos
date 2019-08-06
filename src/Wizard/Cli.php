<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Webos\Wizard;

use salodev\Pli\ComputingEngine;
use salodev\IO\Cli as Base;

/**
 * Description of Cli
 *
 * @author salomon
 */
class Cli extends Base {
	//put your code here
	
	/**
	 * 
	 * @param string $input
	 * @return string
	 */
	static public function parseInput(string $input, array $scopeVars = []): void {
		$ce = new ComputingEngine(Command::class);
		try {
			$scopeVars['wizard'] = new Wizard;
			$ce->evaluate($input, $scopeVars);
		} catch (\Exception $e) {
			echo "ERROR: {$e->getMessage()}\n\nCode:\n";
			$ce->showCurrentParsing(300);
			exit(1);
		}
	}
	
	/**
	 * 
	 * @return string
	 */
	static public function start(array $scopeVars = []): void {
		$input = self::getRawParams();
		self::parseInput($input, $scopeVars);
	}
	
	static public function showHelpText(): void {
		echo " ** WebOS Wizard Command Line Help ** \n";
		echo "\n";
		echo "Avaliable commands:\n";
		echo "    create Useful for create UI elements like Window classes\n";
		echo "    remove Useful for remove created UI elements\n";
		echo "    serve  Serve your application\n";
		echo "\n";
		echo "Example:\n";
		echo "webos create window \n";
		echo "\n";
		echo "This command will start interactively asking for data unless you\n";
		echo "tell by params\n";
		echo "\n";
		echo "To know arguments list type webos <command> help\n";
		echo "\n";
		echo "Enjoy creating useful UIs!\n";
	}
}
