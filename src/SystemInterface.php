<?php
namespace Webos;
use Error;
use ErrorException;
use Exception;
use Webos\VisualObject;
use Webos\Application;
use Webos\Visual\Window;
use Webos\Visual\Windows\Exception as ExceptionWindow;
use Webos\Exceptions\Collection\NotFound;
use Webos\FrontEnd\Page;

class SystemInterface {
	private   $_sessionId         = '';
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

		$system->addEventListener('sessionCreated',      [$this, 'onsSessionCreated']);
		$system->addEventListener('createObject',        [$this, 'onCRUObjects'     ]);
		$system->addEventListener('removeObject',        [$this, 'onCRUObjects'     ]);
		$system->addEventListener('updateObject',        [$this, 'onCRUObjects'     ]);
		$system->addEventListener('loadedWorkSpace',     [$this, 'onLoadedWorkspace'], false);
		$system->addEventListener('authUser',			 [$this, 'onSystemEvent'    ]);
		$system->addEventListener('authUser',            [$this, 'onSystemEvent'    ]);
		$system->addEventListener('loggedIn',            [$this, 'onSystemEvent'    ]);
		$system->addEventListener('sendFileContent',     [$this, 'onSystemEvent'    ]);
		$system->addEventListener('navigateURL',         [$this, 'onSystemEvent'    ]);
		$system->addEventListener('printContent',        [$this, 'onSystemEvent'    ]);

		$system->start();

		$this->_system = $system;
	}

	private function _callAction(string $actionName, string $objectID, array $parameters, bool $ignoreUpdateObject = false): void {
		$this->_resetNotifications();
		$this->lastObjectID = $objectID;
		$this->ignoreUpdateObject = $ignoreUpdateObject;
		$ws = $this->_system->getWorkSpace();
		
		$object = $this->getObjectByID($objectID);
		
		// Security
		if ($object->isDisabled()) {
			throw new Exception('You can not call disabled object');
		}
		
		if ($object->isHidden()) {
			throw new Exception('You can not call hidden object');
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
		$ws->triggerEvent('actionCalled', $this, [
			'actionName' => $actionName,
			'objectId'   => $objectID,
			'object'     => $object,
			'parameters' => $parameters,
		]);

		$object->action($actionName, $parameters);
	}
	
	public function action(string $actionName, string $objectID, array $parameters, bool $ignoreUpdateObject = false): array {
		try {
			$this->_callAction($actionName, $objectID, $parameters, $ignoreUpdateObject);
		} catch (Error $e) {
			$this->showError($e);
		} catch (ErrorException $e) {
			$this->showError($e);
		} catch (Exception $e) {
			$this->showError($e);
		}
		return $this->getParsedNotifications();
	}
	
	public function getOuputSteam(): array {
		$ws = $this->_system->getWorkSpace();
		return $ws->getFileContent()->getArray();
	}
	
	public function getMediaContent(string $objectID, array $params = []): array {
		$object = $this->getObjectByID($objectID);
		return $object->getMediaContent($params)->getArray();
	}
	
	public function getFilestoreDirectory(): string {
		return $this->_system->getWorkSpace()->getFilestoreDirectory();
	}
	
	public function showError($e) {	
		$app = $this->getActiveApplication();

		$parsedException = ExceptionWindow::ParseException($e);
		$w = $app->openMessageWindow('Opps', $e->getMessage());
		if (ENV==ENV_DEV||true) {
			$w->createWindowButton('Details')->onClick(function($context, $source) {
				$source->getApplication()->openWindow(ExceptionWindow::class, $context['exception']);
			}, [
				'exception'=> $parsedException,
			]);
		}
	}
	
	public function renderAll(): string {
		return $this->getWorkSpace()->renderAll();
	}

	public function getActiveApplication(): Application {
		return $this->_system->getWorkspace($this->getSessionId())->getActiveApplication();
	}

	/**
	 * Get alive applications created in the workspace.
	 * @return ApplicationsCollection
	 */
	public function getApplications(): ApplicationsCollection {
		return $this->_system->getWorkspace($this->getSessionId())->getApplications();
	}

	/**
	 *
	 * @return WorkSpace
	 */
	public function getWorkSpace(): WorkSpace {
		return $this->_system->getWorkspace($this->getSessionId());
	}
	/**
	 * AGREGADO: 26-12-2012
	 * @return System
	 */
	public function getSystemInstance(): System {
		return $this->_system;
	}

	public function addCreateNotification(VisualObject $object): self {
		// Verifica si tiene que agregar a la lista de notificaciones.
		if ($this->checkNeccessary($object->getObjectID())) {
			$this->_notifications['create'][$object->getObjectID()] = $object;
		}
		
		return $this;
	}

	public function addUpdateNotification(VisualObject $object): self {
		if ($this->checkNeccessary($object->getObjectID())) {
			$this->_notifications['update'][$object->getObjectID()] = $object;
			$this->_notifications['update_stacks'][] = [
				'stack' => explode("\n", (new Exception)->getTraceAsString()),
				'objectID' => $object->getObjectID(),
			];
		}
		
		return $this;
	}

	public function addRemoveNotification(string $objectId): self {
		$this->_notifications['remove'][] = $objectId;
		return $this;
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
	public function checkNeccessary(string $objectId): bool {
		if ($objectId == $this->lastObjectID) {
			if ($this->ignoreUpdateObject) {
				return false;
			}
		}
		$notif = $this->getNotifications();

		// Verifico objetos a crear.
		if (!empty($notif['create'])) {
			foreach($notif['create'] as $object) {
				if ($object->hasObjectID($objectId)) {
					return false;
				}				
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
				if ($object->hasObjectID($objectId)) {
					return false;
				}

			}
		}

		return true;
	}
	
	public function purgeNotifications(): void {
		$update = $this->_notifications['update'];
		$create = $this->_notifications['create'];
		foreach($update as $updateObject) {
			foreach($create as $y => $createObject) {
				try {
					$updateObject->getObjectByID($createObject->getObjectID());
					unset($create[$y]);
				} catch (Exception $e) {
					
				}
			}
		}
	}

	public function getNotifications(): array {
		$this->purgeNotifications();
		$notifications = $this->_notifications;
		// $this->_resetNotifications();
		return $notifications;
	}
	
	private function _resetNotifications(): void {
		$this->_notifications = [
			'update'  => [],
			'create'  => [],
			'remove'  => [],
			'general' => [],
		];
	}
	
	public function getParsedNotifications(): array {
		
		$notif = $this->_notifications;
		$parsed = [
			'events' => [],
		];
		
		foreach($this->_notifications['general'] as $name => $data) {
			$parsed['events'][] = ['name'=>$name, 'data'=> $data];
		}
			
		// Notificaciones: Actualización.
		/*if (!empty($notif['update_stacks'])){
			$eventData = [];
			foreach($notif['update_stacks'] as $info) {
				$response['events'][] = [
					'name' => 'updateElements_staks',
					'data' => $info,
				];
			}
		}*/

		if (count($notif['update'])){
			$eventData = [];
			foreach($notif['update'] as $object) {
				$content = '';
				try {
					$content = $object->render();
				} catch (NotFound $ex) {

				}
				$eventData[] = [
					'objectId' => $object->getObjectID(),
					'content' => $content,
				];
			}

			$parsed['events'][] = [
				'name' => 'updateElements',
				'data' => $eventData,
			];
		}

		// Notificaciones: Creación.
		if (count($notif['create'])){
			$eventData = [];
			foreach($notif['create'] as $object) {
				$parentObjectID = $object->hasParent() ? $object->getParent()->getObjectID() : '';
				$eventData[] = [
					'parentObjectId' => $parentObjectID,
					'content' => '' . $object->render(),
				];
			}

			$parsed['events'][] = [
				'name' => 'createElements',
				'data' => $eventData,
			];
		}

		// Notificaciones: Eliminación.
		if (count($notif['remove'])) {
			$eventData = [];
			foreach($notif['remove'] as $objectId) {
				$eventData[]['objectId'] = $objectId;
			}

			$parsed['events'][] = [
				'name' => 'removeElements',
				'data' => $eventData,
			];
		}
		
		return $parsed;
	}

	public function setSessionId(string $id): void {
		$this->_sessionId = $id;
	}

	public function getSessionId(): string {
		return $this->_sessionId;
	}

	public function addNotification(string $name, array $data): void {
		$this->_notifications['general'][$name] = $data;
	}
	
	public function onCRUObjects(string $eventName, array $params): void {
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
	
	public function onLoadedWorkspace(System $source): void {
		$ws = $source->getWorkSpace();
		if (!$ws->getApplications()->count()) {
			// $ws->startApplication('\Webos\Apps\Desktop');
		}
	}
	
	public function onsSessionCreated(array $params): void {
		$this->addNotification('sessionCreated', [
			'sessionId' => $params['sessionId'],
		]);
	}
	
	public function onSystemEvent(string $eventName, array $params = []): void {
		$this->addNotification($eventName, $params);
	}
	
	public function getObjectByID(string $objectID): VisualObject {
		$ws = $this->_system->getWorkSpace();

		// First, try get it from the WS Index. (So faster)
		try {
			return $ws->getObjectByID($objectID);
		} catch (NotFound $e) {
			// discard $e;
		}
		
		// Sometimes, object was not indexed, so try walking all objects ware..
		// TODO. Should be removed this check?
		try {
			return $ws->getApplications()->getObjectByID($objectID);
		} catch (NotFound $e) {
			throw new Exception('Object does not exist', null, $e);
		}
	}
}