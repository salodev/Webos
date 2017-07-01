<?php

spl_autoload_register(function($className) {
	$baseDir = dirname(__FILE__) . '/src';
	$className = str_replace('\\','/', $className);
    $classFile = "{$baseDir}/{$className}.php";
    if (file_exists($classFile)) {
        require_once($classFile);
		return;
	}
});