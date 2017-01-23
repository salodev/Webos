<?php
namespace Webos;
abstract class Application extends BaseObject {

	protected $_activeWindow        = null;
	protected $_activeControl       = null;
	protected $_visualObjects       = null;
	protected $_config              = null;
	protected $_workSpace           = null;
	protected $_eventsHandler       = null;
	protected $_systemEventsHandler = null;
	private   $_applicationId       = null;
	
	final public function __construct(WorkSpace $workSpace, $applicationId, array $data = array()) {
		
		parent::__construct($data);

		$this->_applicationId = $applicationId;

		$this->_workSpace           = $workSpace;
		$this->_config              = array();
		$this->_visualObjects       = new ObjectsCollection();
		$this->_eventsHandler       = new EventsHandler();
		$this->_systemEventsHandler = new EventsHandler();

		$workSpace->getApplications()->add($this);

		$this->initialize();
	}

	abstract public function main(array $data = array());

	public function initialize() {}

	public function getApplicationID() {
		return $this->_applicationId;
	}

	public function finish() {
		$this->_workSpace->finishApplication($this);
	}

	/* Tiene que contar con un administrador de eventos */

	public function setWorkSpace($workSpace) {
		$this->_workSpace = $workSpace;
	}

	public function getWorkSpace() {
		return $this->_workSpace;
	}

	public function addEventListener($eventName, $eventListener, $persistent = true) {
		$this->_eventsHandler->addListener($eventName, $eventListener, $persistent);
	}

	public function triggerEvent($eventName, $source, $params) {
		$this->_eventsHandler->trigger($eventName, $source, $params);
	}

	public function addSystemEventListener($eventName, $eventListener, $persistent = true) {
		//$this->getWorkSpace()->addEventListener($eventName, $eventListener, $persistent);
		$this->_systemEventsHandler->addListener($eventName, $eventListener, $persistent);
		return $this;
	}

	public function triggerSystemEvent($eventName, $source, $params = null){
		$this->getWorkSpace()->triggerEvent($eventName, $source, $params);
		//$this->_eventsHandler->trigger($eventName, $source, $params);
		return $this;
	}

	public function notifyEvent($eventName, $source, $params = null) {
		$this->_systemEventsHandler->trigger($eventName, $source, $params);
	}

	/* Administra las ventanas */

	/**
	 *
	 * @param string $windowName
	 * @param array $params
	 * @return Window
	 */
	public function openWindow($windowName, array $params = array(), $relativeTo = null) {
		$window = new $windowName($this, $params);
		$this->setActiveWindow($window);
		if ($relativeTo instanceof Visual\Window) {
			$window->top  = (str_replace('px', '', $relativeTo->top ) + 100) . 'px';
			$window->left = (str_replace('px', '', $relativeTo->left) + 100) . 'px';
		}
		
		return $window;
	}

	public function closeWindow(Visual\Window $window) {
		$objectId = $window->getObjectID();
		$this->_visualObjects->removeObject($window);

		$this->_notifyRemove($objectId);
		/*$this->triggerSystemEvent('removeObject', $this, array(
			'objectId' => $windowID
		));*/

		return $this;
	}

	public function getActiveWindow() {
		return $this->_activeWindow;
	}

	public function setActiveWindow($window) {
		if ($window instanceof Container) {

			$windows = $this->getWorkSpace()->getApplications()->getObjectsByClassName('\Webos\Visual\Window');
			foreach($windows as $wnd) {
				$wnd->modified();
			}
			$this->_activeWindow = $window;
		}
	}
	
	public function openMessageWindow($title, $message) {
		return $this->openWindow('\Webos\Visual\MessageWindow', array(
			'title' => $title,
			'message' => $message,
		), $this->getActiveWindow());
	}

	public function getActiveControl() {
		return $this->_activeControl;
	}

	public function setActiveControl($control) {
		if ($control instanceof ControlObject) {
			$this->_activeControl = $control;
		}
	}

	public function getVisualObjects() {
		return $this->_visualObjects;
	}

	public function getWindow($id) {
		$window = $this->_visualObjects->getObjectByID($id);
		if ($window instanceof Visual\Window) {
			return $window;
		}

		return null;
	}

	public function getWindows() {
		return $this->_visualObjects->getObjectsByClassName('\Webos\Visual\Window');
	}
	
	public function getChildObjects() {
		return $this->_visualObjects;
	}

	public function addChildObject(VisualObject $child) {
		$parent = $child->getParent();
		if (!($parent instanceof Application)) {
			throw new Exception('Trying to add a child object without parent to ' . get_class($this));
		}

		if (get_class($parent) != get_class($this)) {
			throw new \Exception('Object id ' .
				$child->getObjectID() .
				'(' . get_class($child) . ') ' .
				'can not be child of ' . get_class($this)
			);
		}

		$this->_visualObjects->add($child);

		$this->triggerSystemEvent('createObject', $this, array(
			'object' => $child,
		));

		return $this;
	}

	public function removeChildObject(VisualObject $child) {
		$objectId = $child->getObjectID();
		$this->_visualObjects->removeObject($child);

		// Luego de quitar de la colección informo el hecho.
		$this->_notifyRemove($objectId);
		return $this;
	}

	/* Administra su configuración */

	public function getConfig($varName) {
		if (!isset($this->_config[$varName])) return null;
		
		return $this->_config[$varName];
	}

	public function setConfig($varName, $varValue) {
		$this->_config[$varName] = $varValue;
	}

	final public function getObjectByID($id, $horizontal = true) {
		return $this->_visualObjects->getObjectByID($id, $horizontal);
	}
 

	final public function getObjectsByClassName($className) {
		return $this->_visualObjects->getObjectsByClassName($className);
	}

	protected function _notifyRemove($objectId) {
		$this->triggerSystemEvent('removeObject', $this, array(
			'objectId' => $objectId
		));
	}

	/* Información sobre la aplicación */

	abstract public function getName();

	abstract public function getVersion();

	abstract public function getProvider();
}