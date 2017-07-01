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

	/**
	 * @return \Webos\WorkSpace
	 */
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
	 * @return Visual\Window
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
	
	/**
	 * 
	 * @param \Exception $e
	 * @return Visual\ExceptionWindow
	 */
	public function openExceptionWindow(\Exception $e) {
		return $this->openWindow('\Webos\Visual\Windows\Exception', [
			'e' => $e,
		], $this);
	}

	/**
	 * 
	 * @param \Webos\Visual\Window $window
	 * @return $this
	 */
	public function closeWindow(Visual\Window $window) {
		$objectId = $window->getObjectID();
		$this->_visualObjects->removeObject($window);

		$this->_notifyRemove($objectId);
		/*$this->triggerSystemEvent('removeObject', $this, array(
			'objectId' => $windowID
		));*/

		return $this;
	}

	/**
	 * 
	 * @return Visual\Window
	 */
	public function getActiveWindow() {
		return $this->_activeWindow;
	}

	/**
	 * 
	 * @param \Webos\Visual\Container $window
	 * @return $this
	 */
	public function setActiveWindow($window) {
		if ($window instanceof \Webos\Visual\Container) {

			$windows = $this->getWorkSpace()->getApplications()->getObjectsByClassName('\Webos\Visual\Window');
			foreach($windows as $wnd) {
				$wnd->modified();
			}
			$this->_activeWindow = $window;
		}
		return $this;
	}
	
	/**
	 * 
	 * @param type $title
	 * @param type $message
	 * @return Visual\MessageWindow
	 */
	public function openMessageWindow($title, $message) {
		return $this->openWindow('\Webos\Visual\Windows\Message', array(
			'title' => $title,
			'message' => $message,
		), $this->getActiveWindow());
	}

	/**
	 * 
	 * @return Visual\Control
	 */
	public function getActiveControl() {
		return $this->_activeControl;
	}

	/**
	 * 
	 * @param \Webos\Visual\Control $control
	 * @return $this
	 */
	public function setActiveControl(Visual\Control $control) {
		$this->_activeControl = $control;
		return $this;
	}

	/**
	 * 
	 * @return ObjectsCollection
	 */
	public function getVisualObjects() {
		return $this->_visualObjects;
	}

	/**
	 * 
	 * @param type $id
	 * @return Visual\Window
	 */
	public function getWindow($id) {
		$window = $this->_visualObjects->getObjectByID($id);
		if ($window instanceof Visual\Window) {
			return $window;
		}

		return null;
	}

	/**
	 * 
	 * @return ObjectsCollection
	 */
	public function getWindows() {
		return $this->_visualObjects->getObjectsByClassName('\Webos\Visual\Window');
	}

	/**
	 * 
	 * @return ObjectsCollection
	 */
	public function getChildObjects() {
		return $this->_visualObjects;
	}

	/**
	 * 
	 * @param \Webos\VisualObject $child
	 * @return $this
	 * @throws \Exception
	 */
	public function addChildObject(VisualObject $child) {
		$parent = $child->getParent();
		if (!($parent instanceof Application)) {
			throw new \Exception('Trying to add a child object without parent to ' . get_class($this));
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

	/**
	 * 
	 * @param \Webos\VisualObject $child
	 * @return $this
	 */
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