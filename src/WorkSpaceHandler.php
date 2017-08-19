<?php

namespace Webos;
use Webos\WorkSpace;
use Webos\System;

abstract class WorkSpaceHandler {
	protected $_system = null;
	
	final public function __construct(System $system) {
		$this->_system = $system;
	}
	
	abstract public function load(string $name): WorkSpace;
	
	abstract public function store(WorkSpace $workSpace);
	
	abstract public function remove(string $name = null);
}