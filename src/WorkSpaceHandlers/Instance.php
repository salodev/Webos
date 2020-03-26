<?php

namespace Webos\WorkSpaceHandlers;
use Webos\WorkSpaceHandler;
use Webos\WorkSpace;

class Instance extends WorkSpaceHandler{
	
	/**
	 *
	 * @var Webos\WorkSpace;
	 */
	private $_instance = null;
	
	public function load(string $name): WorkSpace {
		if (!$this->_instance instanceof WorkSpace) {
			$fileStorage = new FileSystem($this->_system);
			$this->_instance = $fileStorage->load($name);
		}
		return $this->_instance;
	}
	
	public function store(WorkSpace $workSpace): void {
		$fileStorage = new FileSystem($this->_system);
		$fileStorage->store($workSpace);
	}
	
	public function remove(string $name = null): void {
		// noghing necessary
	}
}