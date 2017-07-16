<?php
namespace Webos;
use \Webos\Visual\Container;
use \Webos\Visual\Window;

abstract class Application {

	protected $_activeWindow        = null;
	protected $_activeControl       = null;
	protected $_visualObjects       = null;
	protected $_config              = null;
	protected $_workSpace           = null;
	protected $_eventsHandler       = null;
	protected $_systemEventsHandler = null;
	
	final public function __construct(WorkSpace $workSpace) {

		$this->_workSpace           = $workSpace;
		$this->_config              = array();
		$this->_visualObjects       = new ObjectsCollection();
		$this->_eventsHandler       = new EventsHandler();
		$this->_systemEventsHandler = new EventsHandler();
	}

	abstract public function main(array $data = array());
	
	public function signalFinish() {}

	final public function finish() {
		$this->_workSpace->finishApplication($this);
	}

	/* Tiene que contar con un administrador de eventos */

	public function setWorkSpace(WorkSpace $workSpace): self {
		$this->_workSpace = $workSpace;
		return $this;
	}

	/**
	 * @return \Webos\WorkSpace
	 */
	public function getWorkSpace(): WorkSpace {
		return $this->_workSpace;
	}

	public function addEventListener(string $eventName, callback $eventListener, bool $persistent = true,  array $contextData = []): self {
		$this->_eventsHandler->addListener($eventName, $eventListener, $persistent, $contextData);
		return $this;
	}

	public function triggerEvent(string $eventName, $source, $params): self {
		$this->_eventsHandler->trigger($eventName, $source, $params);
		return $this;
	}

	public function addSystemEventListener(string $eventName, callback $eventListener, bool $persistent = true,  array $contextData = []): self {
		//$this->getWorkSpace()->addEventListener($eventName, $eventListener, $persistent);
		$this->_systemEventsHandler->addListener($eventName, $eventListener, $persistent, $contextData);
		return $this;
	}

	public function triggerSystemEvent(string $eventName, $source, $params = null): self {
		$this->getWorkSpace()->triggerEvent($eventName, $source, $params);
		//$this->_eventsHandler->trigger($eventName, $source, $params);
		return $this;
	}

	public function notifyEvent(string $eventName, $source, $params = null): self {
		$this->_systemEventsHandler->trigger($eventName, $source, $params);
		return $this;
	}

	/* Administra las ventanas */

	/**
	 *
	 * @param string $windowName
	 * @param array $params
	 * @return Visual\Window
	 */
	public function openWindow(string $windowName = null, array $params = array(), $relativeTo = null): Visual\Window {
		if ($windowName===null) {
			$windowName = Window::class;
		}
		$window = new $windowName($this, $params);
		$this->setActiveWindow($window);
		if ($relativeTo instanceof Window) {
			$window->top  = $relativeTo->top  + 100;
			$window->left = $relativeTo->left + 100;
		}
		
		return $window;
	}
	
	/**
	 * 
	 * @param \Exception $e
	 * @return Visual\ExceptionWindow
	 */
	public function openExceptionWindow(\Exception $e): Visual\Windows\Exception {
		$params = Visual\Windows\Exception::ParseException($e);
		return $this->openWindow(Visual\Windows\Exception::class, $params, $this);
	}

	/**
	 * 
	 * @param \Webos\Visual\Window $window
	 * @return $this
	 */
	public function closeWindow(Visual\Window $window): self {
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
	public function getActiveWindow(): Window {
		return $this->_activeWindow;
	}

	/**
	 * 
	 * @param \Webos\Visual\Container $window
	 * @return $this
	 */
	public function setActiveWindow(Container $window): self {
		$windows = $this->getWorkSpace()->getApplications()->getObjectsByClassName(Window::class);
		foreach($windows as $wnd) {
			$wnd->modified();
		}
		$this->_activeWindow = $window;
		return $this;
	}
	
	/**
	 * 
	 * @param type $title
	 * @param type $message
	 * @return Visual\MessageWindow
	 */
	public function openMessageWindow(string $title, string $message): Visual\Windows\Message {
		return $this->openWindow(Visual\Windows\Message::class, array(
			'title' => $title,
			'message' => $message,
		), $this->getActiveWindow());
	}

	/**
	 * 
	 * @return Visual\Control
	 */
	public function getActiveControl(): Visual\Control {
		return $this->_activeControl;
	}

	/**
	 * 
	 * @param \Webos\Visual\Control $control
	 * @return $this
	 */
	public function setActiveControl(Visual\Control $control): self {
		$this->_activeControl = $control;
		return $this;
	}

	/**
	 * 
	 * @return ObjectsCollection
	 */
	public function getVisualObjects(): ObjectsCollection {
		return $this->_visualObjects;
	}

	/**
	 * 
	 * @param type $id
	 * @return Visual\Window
	 */
	public function getWindow(string $id): Window {
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
	public function getWindows(): ObjectsCollection {
		return $this->_visualObjects->getObjectsByClassName('\Webos\Visual\Window');
	}

	/**
	 * 
	 * @return ObjectsCollection
	 */
	public function getChildObjects(): ObjectsCollection {
		return $this->_visualObjects;
	}

	/**
	 * 
	 * @param \Webos\VisualObject $child
	 * @return $this
	 * @throws \Exception
	 */
	public function addChildObject(VisualObject $child): self {
		
		if ($child->getApplication() !== $this) {
			throw new \Exception('Child object to add must be created by same application.');
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
	public function removeChildObject(VisualObject $child): self {
		$objectId = $child->getObjectID();
		$this->_visualObjects->removeObject($child);

		// Luego de quitar de la colección informo el hecho.
		$this->_notifyRemove($objectId);
		return $this;
	}

	final public function getObjectByID(string $id, bool $horizontal = true): VisualObject {
		return $this->_visualObjects->getObjectByID($id, $horizontal);
	}
 

	final public function getObjectsByClassName(string $className): ObjectsCollection {
		return $this->_visualObjects->getObjectsByClassName($className);
	}

	protected function _notifyRemove(string $objectId): self {
		$this->triggerSystemEvent('removeObject', $this, array(
			'objectId' => $objectId
		));
		return $this;
	}

	/* Información sobre la aplicación */

	abstract public function getName(): string;

	abstract public function getVersion(): string;

	abstract public function getProvider(): string;
}