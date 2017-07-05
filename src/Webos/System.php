<?php
namespace Webos;
class System {

	private $_eventsHandler  = null;
	private $_sessionHandler = null;
	private $_workSpaceName  = null;

	private $_workSpace = null;
	private $_config = null;

	public function __construct() {
		$this->_eventsHandler = new EventsHandler();
	}

	public function start() {
		// $this->triggerEvent('start', $this);
		// $this->triggerEvent('globalConfigRequested', $this);
	}
	
	/**
	 * 
	 * @param string $name
	 * @return \Webos\WorkSpace
	 */
	public function createWorkSpace($name) {
		$ws = new WorkSpace($name);
		$ws->setSystemEnvironment($this);
		$this->triggerEvent('createdWorkspace', $this, array(
			'ws' => $ws,
		));
		
		return $ws;
	}
	
	public function loadCreateWorkSpace($name) {
		if (!$this->_workSpace) {
			$ws = $this->createWorkSpace($name);
			$ws->setSystemEnvironment($this);
			$this->_workSpace = $ws;

			$this->triggerEvent('loadedWorkSpace', $this, array(
				'workspace'=>$ws
			));
			$this->_workSpaceName = $name;
		}
		return $this->_workSpace;
	}
	
	public function loadWorkSpace($name) {
		$this->_workSpaceName = $name;

		if ($this->_workSpace) {
			return $this->_workSpace;
		}

		$wsFileName = $this->getConfig('path/workspaces') . $name;
		if (!is_file($wsFileName)) {
			$ws = $this->createWorkSpace($name);
		} else {
			$content = file_get_contents($wsFileName);
			$ws = unserialize($content);
			if (!$ws instanceof WorkSpace) {
				$ws = $this->createWorkSpace($name);
			}
		}
		
		/**
		 * NOTA:
		 * Por accidente (o por el diseño del sistema) queda almacenado
		 * en el WorkSpace la instancia del objeto System. Esto es incorrecto
		 * pero es necesario que el WorkSpace pueda enviar mensajes al
		 * sistema que lo contiene.
		 * 
		 * Es por eso que cuando se recupera del disco duro, tiene que 
		 * volver a indicarse la instacia actual del sistema para que
		 * trabajen los controladores que se configuran cuando éste se
		 * inicia.
		 **/ 
		$ws->setSystemEnvironment($this);
		$this->_workSpace = $ws;

		$this->triggerEvent('loadedWorkSpace', $this, array(
			'workspace'=>$ws
		));

		return $ws;
	}
	
	public function removeWorkSpace() {
		$wsFileName = $this->getConfig('path/workspaces') . $this->_workSpaceName;
		unlink($wsFileName);
	}
	
	public function storeWorkSpace() {
		if (!$this->_workSpaceName || !($this->_workSpace instanceof WorkSpace)) {
			return;
		}
		$wsFileName = $this->getConfig('path/workspaces') . $this->_workSpaceName;
		file_put_contents($wsFileName, serialize($this->_workSpace), FILE_IGNORE_NEW_LINES);
	}

	/**
	 * 
	 * @return WorkSpace
	 * @throws \Exception
	 */
	public function getWorkSpace() {
		if ($this->_workSpace) {
			return $this->_workSpace;
		}
		throw new \Exception('No workspace loaded');
	}

	public function addEventListener($eventName, $eventListener, $persistent = true) {
		$this->_eventsHandler->addListener($eventName, $eventListener, $persistent);
		return $this;
	}

	public function triggerEvent($eventName, $source, $params = null) {
		$this->_eventsHandler->trigger($eventName, $source, $params);
		return $this;
	}

	public function setConfig($name, $value) {
		$this->_config[$name] = $value;
	}

	public function getConfig($name) {
		$it = &$this->_config[$name];
		if (isset($it)) return $it;

		return null;
	}

	public function getAllConfig() {
		return $this->_config;
	}

	public function __destruct() {
		$this->storeWorkSpace();
	}

}