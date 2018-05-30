<?php
namespace Webos;

use Webos\Visual\Window;

/**
 * El WorkSpace es el entorno donde se ejecutarán todas las posibles
 * aplicaciones.
 * Éste da soporte de eventos y comunicación entre aplicaciones. También
 * proporciona a las mismas acceso al objeto System que la contiene.
 **/
class WorkSpace {
	protected $_applications = null;
	protected $_activeApplication = null;
	protected $_eventsHandler = null;
	protected $_name = null;
	protected $_info = [];

	protected $_systemEnvironment = null;
	
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
		$this->_applications = new ApplicationsCollection();
		$this->_eventsHandler = new EventsHandler();
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
	 * Éste método finaliza una aplicación previamente cerrando sus ventanas
	 * abiertas.
	 *
	 * @todo: Este método debería detenerse si una llamada a closeWindow()
	 *        retorna FALSE.
	 *        Debería poseer además un parámetro para forzar el cierre de la
	 *        aplicación sin importar el resultado del método.
	 *        Otra forma de cerrar la aplicación sería indicando que se cierre
	 *        sin llamar a closeWindow().
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
}