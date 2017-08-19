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
		if ($this->_instance instanceof WorkSpace) {
			return $this->_instance;
		}		
		return $this->createWorkSpace($name);
	}
	
	public function store(WorkSpace $workSpace) {
		// nothing necessary
	}
	
	public function remove(string $name = null) {
		// noghing necessary
	}
}