<?php
namespace Webos;
class SystemInterface {
	const PRUEBA = 1;
	protected $_renderer      = null;
	protected $_system        = null;
	protected $_notifications = null;
	private   $_sessionId     = null;

	public function __construct() {

		/**
		 * Establece parámetros iniciales.
		 **/
		$this->_notifications = array(
			'update' => array(),
			'create' => array(),
			'remove' => array(),
			'general' => array(),
		);
		
		$system = new System();

		$system->addEventListener('sessionCreated',  array($this, 'onsSessionCreated'));
		$system->addEventListener('createObject',    array($this, 'onCRUObjects'));
		$system->addEventListener('removeObject',    array($this, 'onCRUObjects'));
		$system->addEventListener('updateObject',    array($this, 'onCRUObjects'));
		$system->addEventListener('loadedWorkSpace', array($this, 'onLoadedWorkspace'), false);

		$system->start();

		$this->_system = $system;
	}

	public function action($actionName, $objectID, $parameters) {
		$ws = $this->_system->getWorkSpace(/*$this->getSessionId()*/);
		$apps = $object = $ws->getApplications();
		// inspect($apps); die();
		$object = $ws->getApplications()->getObjectByID($objectID);		
		// inspect($object); die();
		if (!($object instanceof VisualObject)) return false;

		// Se activa la aplicación antes de efectuar la acción. //
		$app = $object->getParentApp();
		$ws->setActiveApplication($app);

		// Se activa el formulario al que pertenece el objeto antes
		// de efectuar la acción //
		if (!($object instanceof \Webos\Visual\Window)) {
			
			$window = $object->getParentWindow();

			if ($window instanceof VisualObject){
				//$window->active = true;
				$app->setActiveWindow($window);
			}
		} else {
			$app->setActiveWindow($object);
		}

		// Esto permite que las aplicaciones puedan actuar en consecuencia
		// de las acciones del usuario.		
		$ws->triggerEvent('actionCalled', $this, array(
			'actionName' => $actionName,
			'objectId'   => $objectID,
			'parameters' => $parameters,
		));

		$object->action($actionName, $parameters);
	}

	public function getActiveApplication() {
		return $this->_system->getWorkspace($this->getSessionId())->getActiveApplication();
	}

	/**
	 * Get alive applications created in the workspace.
	 * @return ApplicationsCollection
	 */
	public function getApplications() {
		return $this->_system->getWorkspace($this->getSessionId())->getApplications();
	}

	/**
	 *
	 * @return WorkSpace
	 */
	public function getWorkSpace() {
		return $this->_system->getWorkspace($this->getSessionId());
	}
	/**
	 * AGREGADO: 26-12-2012
	 * @return System
	 */
	public function getSystemInstance() {
		return $this->_system;
	}

	public function addCreateNotification(VisualObject $object){
		
		//Log::write('CREATE: ' . $object->getObjectID() . "\n");
		// Verifica si tiene que agregar a la lista de notificaciones.
		if ($this->checkNeccessary($object->getObjectID())) {
			$this->_notifications['create'][] = $object;
		}
	}

	public function addUpdateNotification(VisualObject $object){

		//Log::write('UPDATE: ' . $object->getObjectID() . "\n" . 'parent: ' . $object->getParentWindow()->getObjectId());
		// Verifica si tiene que agregar a la lista de notificaciones.
		if ($this->checkNeccessary($object->getObjectID())) {
			$this->_notifications['update'][] = $object;
		}
	}

	public function addRemoveNotification($objectId){
		$this->_notifications['remove'][] = $objectId;
	}

	/**
	 * Este método verifica si es necesario agregar una notificación de
	 * eliminar objeto, agregar o actualizar.
	 *
	 * Para ello busca en los que pueden ser contenedores del mismo,
	 * y si ya existe algún contenedor apuntado para creación o actualización,
	 * entonces no será necesario notificar.
	 * 
	 * @param <type> $objectId
	 * @return <type>
	 */
	public function checkNeccessary($objectId) {
		$notif = $this->getNotifications();

		// Verifico objetos a crear.
		if (!empty($notif['create'])) {
			foreach($notif['create'] as $object) {
				// Log::write(' - ALREADY CREATED: ' . $object->getObjectID());
				$test = $object->getObjectByID($objectId);

				// Si es contenedor, no es necesario notificar.
				if ($test instanceof VisualObject) {
					// Log::write('El objeto ' . $test->getObjectID() . ' no necesita notificarse.');
					return false;
				}
			}
		}


		// Verifico objetos a actualizar.
		if (!empty($notif['update'])) {
			foreach($notif['update'] as $object) {
				/**
				 * El método verificará que no exista ya en la lista de actualizados
				 * ya que aunque múltiples veces se modifica el objeto durante la
				 * ejecución sólo es importante saber que fue modificado.
				 *
				 **/
				if ($object->getObjectID() == $objectId) {
					return false;
				}

				/**
				 * Ahora verificamos que no haya algún contenedor en la lista de
				 * actualizaciones.
				 */
				$test = $object->getObjectByID($objectId);

				// Si es contenedor, no es necesario notificar.
				if ($test instanceof VisualObject) {
					return false;
				}

			}
		}

		return true;
	}

	public function getNotifications() {
		return $this->_notifications;
	}

	public function setSessionId($id) {
		$this->_sessionId = $id;
	}

	public function getSessionId() {
		return $this->_sessionId;
	}

	public function addNotification($name,$data) {
		$this->_notifications['general'][$name] = $data;
	}
	
	public function onCRUObjects($source, $eventName, $params) {
		switch($eventName) {
			case 'createObject':
				if (isset($params['object'])){
					$this->addCreateNotification($params['object']);
				}
			break;

			case 'removeObject':
				if (isset($params['objectId'])){			
					$this->addRemoveNotification($params['objectId']);
				}
			break;

			case 'updateObject':		
				if (isset($params['object'])){
					//echo "agregando el cambio del objeto " . $params['object']->getObjectID();
					$this->addUpdateNotification($params['object']);

				}
			break;
		}
	}
	
	public function onLoadedWorkspace($source, $eventName, $params) {
		$ws = $source->getWorkSpace();
		if (!$ws->getApplications()->count()) {
			$ws->startApplication('\Webos\Apps\Desktop');
		}
	}
	
	public function onsSessionCreated($source, $eventName, $params) {
		$this->addNotification('sessionCreated', array(
			'sessionId' => $params['sessionId'],
		));
	}
}