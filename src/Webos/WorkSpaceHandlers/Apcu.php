<?php

namespace Webos\WorkSpaceHandlers;
use Webos\WorkSpaceHandler;
use Webos\WorkSpace;

class Apcu extends WorkSpaceHandler{
	
	public function load(string $name): WorkSpace {
		$ws = apc_fetch($name);

		if (!$ws instanceof WorkSpace) {
			$ws = $this->createWorkSpace($name);
		}
		return new $ws;
	}
	
	public function store(WorkSpace $workSpace) {
		apc_store($workSpace->getName(), $workSpace);
		return true;
	}
	
	public function remove(string $name = null) {
		apc_delete($name);
	}
}