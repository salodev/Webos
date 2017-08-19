#!/usr/bin/php
<?php

require_once(dirname(dirname(__FILE__))) . '/autoload.php';
spl_autoload_register(function($className) {
	$baseDir = dirname(dirname(dirname(__FILE__)));
	$className = str_replace('\\','/', $className);
    $classFile = "{$baseDir}/{$className}.class.php";
    if (file_exists($classFile)) {
        require_once($classFile);
		return;
	}
});

use Webos\Service\Server;

$server = new Server();

echo "server started...\n\n";
$server->start('127.0.0.1', 3000);