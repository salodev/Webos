<?php

namespace Webos\Implementations\Service\LocalService;

use Webos\Webos;
use Webos\Implementations\Service\Service;
use Webos\SystemInterface;
use Webos\WorkSpaceHandlers\FileSystem AS FileSystemHanlder;
use salodev\Debug\ObjectInspector;

class LocalService implements Service {

	protected $_workSpaceHandler  = FileSystemHanlder::class;
	protected $userName           = null;
    public    $authData           = null;
	
	static public function SetWorkSpaceHandlerClassName(string $workSpaceHandlerClassName) {
		static::$_workSpaceHandler = $workSpaceHandlerClassName;
	}
	
	protected function getApplicationName(): string {
		return Webos::GetApplicationName();
	}
	
	protected function getApplicationParams(): array {
		return Webos::GetApplicationParams();
	}
	
	protected function getInterface(): SystemInterface {
		static $interface = null;
		if (!($interface instanceof SystemInterface)) {
			$interface = new SystemInterface();
			$interface->run($this->userName, $this->getApplicationName(), $this->getApplicationParams(), $_SERVER['HTTP_USER_AGENT'], $this->_workSpaceHandler);
			$interface = $interface;
		}
		return $interface;
	}
	
	public function auth(string $userName, array $authParams): void {
		$authData = Webos::Authenticate($userName, $authParams);
		$this->userName = $userName;
        $this->authData = $authData;
	}
	
	public function renderAll(): string {
		$output = $this->getInterface()->renderAll();
		return $output;
	}
	
	public function action(string $name, string $objectID, array $parameters, bool $ignoreUpdateObject = false): array {
		return $this->getInterface()->action($name, $objectID, $parameters, $ignoreUpdateObject);
	}
	
	public function getOutputStream(): array {
		return $this->getInterface()->getOutputStream();
	}
	
	public function getMediaContent(string $objectID, array $params = []): array {
		return $this->getInterface()->getMediaContent($objectID, $params);
	}
	
	public function getFilestoreDirectory(): string {
		return $this->getInterface()->getFilestoreDirectory();
	}
	
	public function debug(): void {
		ObjectInspector::inspect($this->getInterface());
		die();
	}
	
	public function setViewportSize(int $width, int $height): void {
		$this->getInterface()->setViewportSize($width, $height);
	}
}