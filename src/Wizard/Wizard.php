<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Webos\Wizard;
use Webos\StringChar;

/**
 * Description of Wizard
 *
 * @author salomon
 */
class Wizard {
	//put your code here
	
	private function getTemplatesBasePath(): string {
		return __DIR__ . '/code_templates/';
	}
	
	private function getProjectBasePath(): string {
		return '';
	}
	
	public function createWindow(array $params = []): string {
		if (empty($params['name'])) {
			$params['name'] = readline('Give class name for new window: ');
		}
		
		if (empty($params['title'])) {
			$params['title'] = readline('Give title for new window: ');
		}
		
		if (empty($params['extends-from'])) {
			$params['extends-from'] = readline('Give class name from extends new window: ');
		}
		print_r($params);
		$classFile = $params['classPath'];
		$classFile .= str_replace('\\', '/', $params['name']) . '.php';
		
		echo "File will be crated into: \n\n{$classFile}\n\n";
		$sn = strtolower(readline('Is correct? (yes): '));
		if ($sn == 'no') {
			$clsasFile = readline('Specify new path: ');
		}
		
		$params['classFile'] = $classFile;
		
		print_r($params);
		die();
		
		$classParts = explode('\\', $fullName);
		$className = array_pop($classParts);
		$namespaceName = implode('\\', $classParts);
		$template = new StringChar(file_get_contents($this->getTemplatesBasePath() . 'window.tpl'));
		$template->replaces([
			'__namespaceName__' => $namespaceName,
			'__className__'     => $className,
			'__windowTitle__'   => $title,
		]);
		
		return $template;
	}
}
