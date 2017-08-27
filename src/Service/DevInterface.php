<?php

namespace Webos\Service;
use Webos\SystemInterface;
use Webos\WorkSpaceHandlers\FileSystem AS FileSystemHanlder;
use salodev\Debug\ObjectInspector;

class DevInterface implements UserInterface {
	
	private $_user            = null;
	public  $_applicationName = null;
	private $_interface = null;
	private $_system    = null;
	
	public function __construct(string $userName, string $applicationName, array $applicationParams = []) {
		$this->_user = $userName;
		$this->_applicationName   = $applicationName;
		$this->_applicationParams = $applicationParams;
		$this->_interface = new SystemInterface();
		$this->_system = $this->_interface->getSystemInstance();
		$this->_system->setConfig('path/workspaces', PATH_PRIVATE . 'workspaces/');
		$this->_system->setWorkSpaceHandler(new FileSystemHanlder($this->_system));
		
		$this->_system->addEventListener('createdWorkspace', function($data) {
			$data['ws']->startApplication($this->_applicationName, $this->_applicationParams);
		});
		$this->_system->loadWorkSpace($userName);
		
	}
	
	public function renderAll(): string {
		return $this->_interface->renderAll();
	}
	
	public function action(string $name, string $objectID, array $parameters, bool $ignoreUpdateObject = false): array {
		return $this->_interface->action($name, $objectID, $parameters, $ignoreUpdateObject);
	}
	
	public function debug(): void {
		ObjectInspector::inspect($this->_interface);
		die();
	}
}