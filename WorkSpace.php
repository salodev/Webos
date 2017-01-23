<?php
namespace Webos;
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
	protected $_lastApplicationId = null;
	protected $_name = null;

	protected $_systemEnvironment = null;
	
	/**
	 * @returns WorkSpace;
	 */
	static public function LoadFromFile($pathToFile) {
		$file = FileHandler::openFile($pathToFile, 'readwrite');
		$ws = unserialize($file->getContent());
		return $ws['ws'];
	}

	public function __construct($name) {
		$this->_name = $name;
		$this->_applications = new ApplicationsCollection();
		$this->_eventsHandler = new EventsHandler();
		return $this;
	}
	
	public function getName() {
		return $this->_name;
	}

	public function getApplications() {
		return $this->_applications;
	}

	public function getApplicationByID($id) {
		foreach($this->_applications as $application) {
			if ($application->getApplicationID() == $id) {
				return $application;
			}
		}

		return null;
	}

	public function startApplication($name, array $params = array()) {

		$appClassName = $name;// . 'Application';

		$application = new $appClassName($this, $this->_getNewApplicationId());
		
		$this->triggerEvent('startApplication', $this, array(
			'object' => $application,
		));
		
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
	public function finishApplication(Application $application, $method = 1) {
		$applicationKey = 0;
		foreach($this->_applications as $test) {
			if ($test->getApplicationID() == $application->getApplicationID()) {
				$applicationKey = $this->_applications->key();
				break;
			}
		}

		$this->triggerEvent('beforeFinishApplication', $this, array(
			'object' => $application,
		));

		$windows = $application->getObjectsByClassName('\Webos\Visual\Window');
		
		foreach($windows as $window) {
			$application->closeWindow($window);
		}

		$this->_applications->remove($applicationKey);

		$this->triggerEvent('finishApplication', $this, array(
			'object' => $application,
		));
		
		return $this;
	}

	public function setActiveApplication($application) {
		$this->_activeApplication = $application;

		$this->triggerEvent('activeApplication', $this, array(
			'object' => $application,
		));
	}

	public function getActiveApplication() {
		return $this->_activeApplication;
	}

	public function setSystemEnvironment($system) {
		$this->_systemEnvironment = $system;
	}

	public function getSystemEnvironment() {
		return $this->_systemEnvironment;
	}

	public function addEventListener($eventName, $eventListener, $persistent = true) {
		$this->_eventsHandler->addListener($eventName, $eventListener, $persistent);
		return $this;
	}

	public function triggerEvent($eventName, $source, $params = null){
		$this->_eventsHandler->trigger($eventName, $source, $params);

		/**
		 * Las aplicaciones son informadas sobre los eventos del sistema.
		 */
		foreach($this->getApplications() as $application) {
			$application->notifyEvent($eventName, $source, $params);
		}

		/**
		 * Como estoy dando soporte a la capa de transporte a acceder a
		 * los eventos del sistema, encuentro que algunos del WorkSPace
		 * son útiles y necesarios para mejorar la inteligencia de esa capa.
		 * 
		 * Entonces necesito comunicar algunos eventos al sistema.
		 * NO ASÍ la subscripción de observadores.
		 **/
		$this->getSystemEnvironment()->triggerEvent($eventName, $source, $params);		
		return $this;
	}

	public function authUser($username, $password) {
		$this->getSystemEnvironment()->authUser($username, $password);
	}

	private function _getNewApplicationId() {
		$appId = ($this->_applications->count() + 1);
		$this->_lastApplicationId = $appId;

		return $appId;
	}
}