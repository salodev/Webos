<?php

namespace Webos;

use Exception;
use Webos\WorkSpaceHandlers\FileSystem;
use Webos\WorkSpaceHandler;

class System {

	private $_eventsHandler  = null;
	private $_workSpaceName  = null;
	private $_workSpace      = null;
	private $_config         = null;

	/**
	 *
	 * @var Webos\WorkSpaceHandler
	 */
	private $_workSpaceHandler = null;

	public function __construct() {
		$this->_eventsHandler = new EventsHandler();
		$this->_workSpaceHandler = new FileSystem($this);
	}
	
	public function setWorkSpaceHandler(WorkSpaceHandler $workSpaceHandler) {
		$this->_workSpaceHandler = $workSpaceHandler;
	}
	
	/**
	 * 
	 * @param string $name
	 * @return \Webos\WorkSpace
	 */
	public function createWorkSpace(string $name): WorkSpace {
		$ws = new WorkSpace($name);
		$ws->setSystemEnvironment($this);
		$this->triggerEvent('createdWorkspace', $this, [
			'ws' => $ws,
		]);
		
		return $ws;
	}
	
	public function loadWorkSpace($name): WorkSpace {

		if ($this->_workSpace) {
			return $this->_workSpace;
		}
		
		$ws = $this->_workSpaceHandler->load($name);
		$this->setWorkSpace($ws, $name);
		return $ws;
	}
	
	public function setWorkSpace(WorkSpace $ws): self {
		$this->_workSpaceName = $ws->getName();

		/**
		 * Set the new environment instance to workspace in order to
		 * keep in communication with workspace events and it with
		 * system events.
		 * Stored instance will be ever discarded.
		 **/ 
		$ws->setSystemEnvironment($this);
		$this->_workSpace = $ws;
		WorkSpace::SetCurrent($ws);

		$this->triggerEvent('loadedWorkSpace', $this, [
			'workspace'=>$ws
		]);
		
		/**
		 * Just useful for development mode.
		 */
		
		$ws->getApplications()->each(function($application) {
			$application->setup();
		});

		return $this;
	}
	
	public function removeWorkSpace(): self {
		$this->_workSpaceHandler->remove($this->_workSpaceName);
		return $this;
	}
	
	public function storeWorkSpace(): self {
		if (!$this->_workSpaceName || !($this->_workSpace instanceof WorkSpace)) {
			throw new Exception('No workspace');
		}
		$this->_workSpaceHandler->store($this->_workSpace);
		
		return $this;
	}

	/**
	 * 
	 * @return WorkSpace
	 * @throws \Exception
	 */
	public function getWorkSpace(): WorkSpace {
		if ($this->_workSpace) {
			return $this->_workSpace;
		}
		throw new Exception('No workspace loaded');
	}

	public function addEventListener(string $eventName, $eventListener, $persistent = true,  array $contextData = []): self {
		$this->_eventsHandler->addListener($eventName, $eventListener, $persistent, $contextData);
		return $this;
	}

	public function triggerEvent(string $eventName, $source, array $params = []): self {
		$this->_eventsHandler->trigger($eventName, $source, $params);
		return $this;
	}

	public function setConfig($name, $value): self {
		$this->_config[$name] = $value;
		return $this;
	}

	public function getConfig($name) {
		$it = &$this->_config[$name];
		if (isset($it)) return $it;

		return null;
	}

	public function getAllConfig(): array {
		return $this->_config;
	}

	public function __destruct() {
		try {
			$this->storeWorkSpace();
		} catch (Exception $e) {
			
		}
	}

}