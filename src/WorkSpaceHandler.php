<?php

namespace Webos;

use Webos\WorkSpace;
use Webos\System;

/**
 * This abstraction allows to framework set different ways to load and retrieve
 * the user workspace.
 * For each case a special handler is appropiate to set.
 */
abstract class WorkSpaceHandler {
	
	protected $_system = null;
	
	final public function __construct(System $system) {
		$this->_system = $system;
	}
	
	abstract public function load(string $name): WorkSpace;
	
	abstract public function store(WorkSpace $workSpace): void;
	
	abstract public function remove(string $name = null): void;
}