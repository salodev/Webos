<?php

namespace Webos\WorkSpaceHandlers;
use Webos\WorkSpaceHandler;
use Webos\WorkSpace;

class FileSystem extends WorkSpaceHandler{
	
	public function load(string $name): WorkSpace {
		$wsFileName = $this->_system->getConfig('path/workspaces') . $name;
		if (!is_file($wsFileName)) {
			$ws = $this->_system->createWorkSpace($name);
		} else {
			$ws = unserialize(file_get_contents($wsFileName));
			if (!$ws instanceof WorkSpace) {
				$ws = $this->_system->createWorkSpace($name);
			}
		}
		return $ws;
	}
	
	public function store(WorkSpace $workSpace) {
		$wsFileName = $this->_system->getConfig('path/workspaces') . $workSpace->getName();
		file_put_contents($wsFileName, serialize($workSpace), FILE_IGNORE_NEW_LINES);
		return true;
	}
	
	public function remove(string $name = null) {
		$wsFileName = $this->_system->getConfig('path/workspaces') . $name;
		unlink($wsFileName);
	}
}