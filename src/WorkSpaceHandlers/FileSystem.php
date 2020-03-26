<?php

namespace Webos\WorkSpaceHandlers;

use Webos\Webos;
use Webos\WorkSpaceHandler;
use Webos\WorkSpace;

class FileSystem extends WorkSpaceHandler {
	
	public function load(string $name): WorkSpace {
		$wsFileName = static::getFileName($name);
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
	
	public function store(WorkSpace $workSpace): void {
		$wsFileName = static::getFileName($workSpace->getName());
		file_put_contents($wsFileName, serialize($workSpace), FILE_IGNORE_NEW_LINES);
	}
	
	public function remove(string $name = null): void {
		$wsFileName = static::getFileName($name);
		unlink($wsFileName);
	}
	
	static protected function getFileName(string $name): string {
		return Webos::GetWorkSpacesPath() . '/' . $name;
	}
}