<?php
namespace Webos;

use Webos\Visual\Window;
use Webos\FrontEnd\PageWrapper;
use Webos\FrontEnd\Page;

/**
 * WorkSpace is the scope for all applications.
 * It supports events and app communications.
 **/
class WorkSpace {
	
	protected $_applications      = null;
	protected $_activeApplication = null;
	protected $_eventsHandler     = null;
	protected $_name              = null;
	protected $_info              = [];
	protected $_pageWrapper       = null;
	protected $_vpWidth           = 1200;
	protected $_vpHeight          = 600;
	
	/**
	 *
	 * @var Webos\System; 
	 */
	protected $_systemEnvironment = null;
	protected $_outputStream = null;
	protected $_inputStream  = null;
	protected static $_current = null;
	
	static public function SetCurrent(self $workSpace) {
		self::$_current = $workSpace;
	}
	
	static public function Info(string $name, $value = null) {
		if ($value ===null) {
			return self::$_current->getInfo($name, $value);
		} else {
			self::$_current->setInfo($name, $value);
		}
	}
	
	public function setInfo($name, $value) {
		$this->_info[$name] = $value;
	}
	
	public function getInfo($name) {
		return $this->_info[$name];
	}
	
	public function __construct(string $name) {
		$this->_name = $name;
		$this->_applications  = new ApplicationsCollection();
		$this->_eventsHandler = new EventsHandler();
		$this->setPageWrapper(new Page);
		$this->_outputStream  = new Stream\Handler();
		$this->_inputStream   = new Stream\Handler();
		return $this;
	}
	
	public function getName(): string {
		return $this->_name;
	}

	public function getApplications(): ApplicationsCollection {
		return $this->_applications;
	}

	public function startApplication(string $name, array $params = []): self {

		$appClassName = $name;// . 'Application';

		$application = new $appClassName($this);
		$application->setParams($params);		
		
		$this->triggerEvent('startApplication', $this, array(
			'object' => $application,
		));
		
		$this->getApplications()->add($application);
		$this->setActiveApplication($application);
		$application->main($params);

		return $this;
	}

	/**
	 * Finishes application closing all own open windows.
	 *
	 * @todo: This method should stop if a closeWindow() call returns FALSE.
	 *        Should be an optional param to force close appliaction avoiding
	 *	      closeWindow() returns.
	 *        Other way may be closing indicating no call closeWindow()
	 * @param <type> $key
	 * @return WorkSpace
	 */
	public function finishApplication(Application $application): self {

		$this->triggerEvent('beforeFinishApplication', $this, array(
			'object' => $application,
		));
		
		$application->signalFinish();

		$windows = $application->getObjectsByClassName(Window::class);
		// $windows = $application->getVisualObjects();
		
		foreach($windows as $window) {
			$application->closeWindow($window);
		}

		$this->_applications->removeApplication($application);

		$this->triggerEvent('finishApplication', $this, array(
			'object' => $application,
		));
		
		return $this;
	}

	public function setActiveApplication(Application $application): self {
		$this->_activeApplication = $application;

		$this->triggerEvent('activeApplication', $this, array(
			'object' => $application,
		));
		
		return $this;
	}

	public function hasActiveApplication(): bool {
		return $this->_activeApplication instanceof Application;
	}

	public function getActiveApplication(): Application {
		return $this->_activeApplication;
	}

	public function setSystemEnvironment($system): void {
		$this->_systemEnvironment = $system;
	}

	/**
	 * 
	 * @return \Webos\System;
	 */
	public function getSystemEnvironment(): System {
		return $this->_systemEnvironment;
	}

	public function addEventListener($eventName, $eventListener, $persistent = true,  array $contextData = []): self {
		$this->_eventsHandler->addListener($eventName, $eventListener, $persistent, $contextData);
		return $this;
	}

	public function triggerEvent($eventName, $source, $params = null): self {
		$this->_eventsHandler->trigger($eventName, $source, $params);

		/**
		 * Las aplicaciones son informadas sobre los eventos del sistema.
		 */
		foreach($this->getApplications() as $application) {
			$application->notifyEvent($eventName, $source, $params);
		}

		/**
		 * Como estoy dando soporte a la capa de transporte a acceder a
		 * los eventos del sistema, encuentro que algunos del WorkSpace
		 * son útiles y necesarios para mejorar la inteligencia de esa capa.
		 * 
		 * Entonces necesito comunicar algunos eventos al sistema.
		 * NO ASÍ la subscripción de observadores.
		 **/
		$this->getSystemEnvironment()->triggerEvent($eventName, $source, $params);		
		return $this;
	}
	
	public function setPageWrapper(PageWrapper $pageWrapper): void {
		$this->_pageWrapper = $pageWrapper;
	}
	
	public function getPageWrapper(): PageWrapper {
		return $this->_pageWrapper;
	}
	
	public function renderAll(): string {
		$html = $this
				->getApplications()
				->getVisualObjects()
				->render();
		
		return $this->getPageWrapper()->setContent($html)->getHTML();
	}
	
	public function streamContent(string $content = null, string $mimeType = null, string $name = null): self {
		$stream = new Stream\Content($content, $mimeType, $name);
		$this->_outputStream->addContent($stream);
		$this->triggerEvent('sendFileContent', $this, []);
		return $this;
	}
	
	public function streamFile(string $path): self {
		$this->_outputStream->addContent(new Stream\Content('', '', '', $path));
		$this->triggerEvent('sendFileContent', $this, []);
		return $this;
	}
	
	public function printContent(string $content): self {
		$this->triggerEvent('printContent', $this, [
			'content' => $content,
		]);
		return $this;
	}
	
	public function getFileContent(): Stream\Content {
		return $this->_outputStream->getLastAndRemove();
	}
	
	public function getFilestoreDirectory(): string {
		$configPath = $this->_systemEnvironment->getConfig('path/workspaces');
		
		return "{$configPath}{$this->_name}/files/";
	}
	
	public function setViewportSize(int $width, int $height) {
		$this->_vpWidth = $width;
		$this->_vpHeight = $height;
	}
	
	public function getViewportWidth(): int {
		return $this->_vpWidth;
	}
	
	public function getViewportHeight(): int {
		return $this->_vpHeight;
	}
}