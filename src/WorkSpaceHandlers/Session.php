<?php

namespace Webos\WorkSpaceHandlers;
use Webos\WorkSpaceHandler;
use Webos\WorkSpace;

class Session extends WorkSpaceHandler{
	
	public function load(string $name): WorkSpace {
		$ws = $_SESSION['ws']??null;
		if (!$ws instanceof WorkSpace) {
			$ws = $this->_system->createWorkSpace($name);
			$_SESSION['ws'] = $ws;
		}
		return $ws;
	}
	
	public function store(WorkSpace $workSpace) {
		$_SESSION['ws'] = $workSpace;
		return true;
	}
	
	public function remove(string $name = null) {
		$_SESSION['ws'] = null;
	}
}