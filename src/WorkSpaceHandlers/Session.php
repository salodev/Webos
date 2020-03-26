<?php

namespace Webos\WorkSpaceHandlers;

use Webos\WorkSpaceHandler;
use Webos\WorkSpace;

/**
 * This handler stores WorkSpace on website session
 * but no handling how session is stored.
 * Because it, you are free to setup session handlers.
 */
class Session extends WorkSpaceHandler {
	
	static public $stopStore = false;
	
	/**
	 * Load or create a workspace for a given user name
	 */
	public function load(string $name): WorkSpace {
		$ws = $_SESSION['ws']??null;
		if (!$ws instanceof WorkSpace) {
			$ws = $this->_system->createWorkSpace($name);
			$_SESSION['ws'] = $ws;
		}
		return $ws;
	}
	
	/**
	 * Store it in ws session variable
	 */
	public function store(WorkSpace $workSpace): void {
		if (!static::$stopStore) {
			$_SESSION['ws'] = $workSpace;
		}
	}
	
	/**
	 * remove it by setting null
	 */
	public function remove(string $name = null): void {
		$_SESSION['ws'] = null;
	}
}