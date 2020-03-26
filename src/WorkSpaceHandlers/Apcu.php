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
	
	public function store(WorkSpace $workSpace): void {
		apc_store($workSpace->getName(), $workSpace);
	}
	
	public function remove(string $name = null): void {
		apc_delete($name);
	}
}