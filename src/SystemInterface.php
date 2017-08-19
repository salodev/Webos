<?php
namespace Webos;
use Exception;
use Webos\VisualObject;
use Webos\Application;
use Webos\Visual\Window;
use Webos\Exceptions\Collection\NotFound;

class SystemInterface {
	private   $_sessionId         = null;
	protected $_renderer          = null;
	protected $_system            = null;
	protected $_notifications     = null;
	public    $lastObjectID       = null;
	public    $ignoreUpdateObject = false;

	public function __construct() {

		/**
		 * Establece parámetros iniciales.
		 **/
		$this->_resetNotifications();
		
		$system = new System();

		$system->addEventListener('sessionCreated',  array($this, 'onsSessionCreated'));
		$system->addEventListener('createObject',    array($this, 'onCRUObjects'));
		$system->addEventListener('removeObject',    array($this, 'onCRUObjects'));
		$system->addEventListener('updateObject',    array($this, 'onCRUObjects'));
		$system->addEventListener('loadedWorkSpace', array($this, 'onLoadedWorkspace'), false);

		$system->start();

		$this->_system = $system;
	}

	public function action($actionName, $objectID, $parameters, $ignoreUpdateObject = false) {
		$this->_resetNotifications();
		$this->lastObjectID = $objectID;
		$this->ignoreUpdateObject = $ignoreUpdateObject;
		$ws = $this->_system->getWorkSpace();
		try {
			$object = $ws->getApplications()->getObjectByID($objectID);
		} catch (NotFound $e) {
			throw new Exception('Object does not exist', null, $e);
		}

		// Se activa la aplicación antes de efectuar la acción. //
		$ws->setActiveApplication($object->getApplication());

		// Se activa el formulario al que pertenece el objeto antes
		// de efectuar la acción //
		// Los termine anulando porque es lo que genera
		// que se redibuje toda la ventana luego de actualizar un campo.
		if (!($object instanceof Window)) {
			
			$window = $object->getParentWindow();

			if ($window instanceof VisualObject){
				// $app->setActiveWindow($window);
			}
		} else {
			// $app->setActiveWindow($object);
		}

		// Esto permite que las aplicaciones puedan actuar en consecuencia
		// de las acciones del usuario.		
		$ws->triggerEvent('actionCalled', $this, array(
			'actionName' => $actionName,
			'objectId'   => $objectID,
			'object'     => $object,
			'parameters' => $parameters,
		));

		$object->action($actionName, $parameters);
	}

	public function getActiveApplication(): Application {
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
			$this->_notifications['update_stacks'][] = [
				'stack' => explode("\n", (new \Exception)->getTraceAsString()),
				'objectID' => $object->getObjectID(),
			];
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
		if ($objectId == $this->lastObjectID) {
			if ($this->ignoreUpdateObject) {
				return false;
			}
		}
		$notif = $this->getNotifications();

		// Verifico objetos a crear.
		if (!empty($notif['create'])) {
			foreach($notif['create'] as $object) {
				// if ($object instanceof Visual\Window) {
					if ($object->hasObjectID($objectId)) {
						return false;
					}
				// }
				if ($object->getObjectID() == $objectId) {
					return false;
				}
			}
			//return true;
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
				try {
					$test = $object->getObjectByID($objectId);
				} catch (NotFound $e) {
					return true;
				}

				// Si es contenedor, no es necesario notificar.
				if ($test instanceof VisualObject) {
					return false;
				}

			}
		}

		return true;
	}

	public function getNotifications() {
		$notifications = $this->_notifications;
		// $this->_resetNotifications();
		return $notifications;
	}
	
	private function _resetNotifications() {
		$this->_notifications = array(
			'update' => array(),
			'create' => array(),
			'remove' => array(),
			'general' => array(),
		);
	}

	public function setSessionId($id) {
		$this->_sessionId = $id;
	}

	public function getSessionId() {
		return $this->_sessionId;
	}

	public function addNotification($name,$data) {
		echo "\n*********************************";
		echo "\n* addNotification: {$name}";
		echo "\n*********************************\n";
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
					$this->addUpdateNotification($params['object']);

				}
			break;
		}
	}
	
	public function onLoadedWorkspace($source, $eventName, $params) {
		$ws = $source->getWorkSpace();
		if (!$ws->getApplications()->count()) {
			// $ws->startApplication('\Webos\Apps\Desktop');
		}
	}
	
	public function onsSessionCreated($source, $eventName, $params) {
		$this->addNotification('sessionCreated', array(
			'sessionId' => $params['sessionId'],
		));
	}
}