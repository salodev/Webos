<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Webos\Wizard;

use salodev\Pli\Tokens\Token;
use salodev\IO\IO;

/**
 * Description of Command
 *
 * @author salomon
 */
class Command extends Token {
	
	protected $_mainCommands = [
		'create',
		'remove',
		'serve',
	];
	
	//put your code here
	public function parse(bool $evaluate = false): bool {
		$wizard = $this->readVariable('wizard');
		$this->eatSpaces();
		$word = $this->eatWord();
		if ($word == null) {
			Cli::showHelpText();
			return true;
		}
		if($word == 'create') {
			if ($this->eatExpectedString('window')) {
				$this->createWindow();
				return true;
			}
			return true;
		}
		if($word == 'remove') {
			if ($this->eatString('window')) {
				$windowName = $this->eatWord(' ', $windowName);
				$wizard->removeWindow($windowName);
				return true;
			}
		}
		if ($word == 'serve') {
			$host = '127.0.0.1';
			$host = $this->eatWord()??'127.0.0.1';
			$port = $this->eatWord()??'8080';
			$wizard->serve($host, $port);
			return true;
		}
		$this->raiseError("Unsupported command '{$word}'");
	}
	
	public function createWindow(bool $evaluate = false): void {
		$params = $this->parseParams($evaluate);
		$params['classPath'] = $this->readVariable('classPath');
		$this->readVariable('wizard')->createWindow($params);
	}
	
	public function parseParams(bool $evaluate = false): array {
		$params = [];
		$this->_computingEngine->showCurrentParsing();
		while(!$this->isOver()) {
			$this->eatSpaces();
			$name = $this->eatChars('abcdefghijklmnopqrstuvwxyz-');
			if (!$name) {
				break;
			}
			$this->eatSpaces();
			$this->eatExpectedString('=');
			$this->eatSpaces();
			if ($this->eatString("'")) {
				if (!$this->eatUntil("'", $value)) {
					$this->raiseError('Expected end of string');
				}
			} else {
				$value = $this->eatWord();
			}
			$this->eatSpaces();
			$params[$name] = $value;
		}
		
		return $params;
	}
}
