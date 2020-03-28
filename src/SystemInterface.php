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

/**
 * Originally this class was a real interface between
 * System instance and web controller requests receiver
 * But new features bring it behind new layers between the web controller
 * and SystemInterface.
 * I dont know how to name it. But its main goal is lead with actions and
 * notifications in order to keep browser rener updated about what is present
 * or not on the server stored WorkSpace
 */
class SystemInterface {
	
	protected $_renderer          = null;
	protected $_system            = null;
	protected $_notifications     = null;
	public    $lastObjectID       = null;
	public    $ignoreUpdateObject = false;

	public function __construct() {

		/**
		 * Drop all unconsumed buffered notifications.
		 **/
		$this->_resetNotifications();
		
		$system = new System();

		/**
		 * By event subscription will be able to know once anything happen,
		 * so notify properly to requester.
		 */
		$system->addEventListener('sessionCreated',      [$this, 'onsSessionCreated']);
		$system->addEventListener('createObject',        [$this, 'onCRUObjects'     ]);
		$system->addEventListener('hideObject',          [$this, 'onCRUObjects'     ]);
		$system->addEventListener('showObject',          [$this, 'onCRUObjects'     ]);
		$system->addEventListener('removeObject',        [$this, 'onCRUObjects'     ]);
		$system->addEventListener('updateObject',        [$this, 'onCRUObjects'     ]);
		$system->addEventListener('loadedWorkSpace',     [$this, 'onLoadedWorkspace'], false);
		$system->addEventListener('authUser',			 [$this, 'onSystemEvent'    ]);
		$system->addEventListener('authUser',            [$this, 'onSystemEvent'    ]);
		$system->addEventListener('loggedIn',            [$this, 'onSystemEvent'    ]);
		$system->addEventListener('sendFileContent',     [$this, 'onSystemEvent'    ]);
		$system->addEventListener('navigateURL',         [$this, 'onSystemEvent'    ]);
		$system->addEventListener('printContent',        [$this, 'onSystemEvent'    ]);

		$this->_system = $system;
	}
	
	public function run(string $userName, string $applicationName, array $applicationParams, string $userAgent, string $workSpaceHandlerClasName): void {
		$system = $this->getSystemInstance();
		$system->setWorkSpaceHandler(new $workSpaceHandlerClasName($system));
		
		/**
		 * Because callback function is stored with a special Closure artifact, not supporting 'use' definition in callback.
		 * Scope variables must be passed by third parameter to addEventListener method.
		 */
		$this->_system->addEventListener('createdWorkspace', function($data, $scope) {
			$data['ws']->checkUserAgent($scope['userAgent']);
			$data['ws']->startApplication($scope['applicationName'], $scope['applicationParams']);
		}, false, ['applicationName' => $applicationName, 'applicationParams' => $applicationParams, 'userAgent' => $userAgent]);
		$this->_system->loadWorkSpace($userName);
	}

	private function _callAction(string $actionName, string $objectID, array $parameters, bool $ignoreUpdateObject = false): void {
		//die('<pre>'.\salodev\Debug\ExceptionDumper::DumpFromThrowable(new \Exception('e')));
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
	
	public function getOutputStream(): array {
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
	
	public function setViewportSize($width, $height): bool {
		$this->_system->getWorkSpace()->setViewportSize($width/1, $height/1);
		return true;
	}
	
	public function showError($e) {
		$app = $this->getActiveApplication();

		$parsedException = \salodev\Debug\ExceptionDumper::ParseFromThrowable($e);
		$w = $app->openMessageWindow('Opps', $e->getMessage());
		if (Webos::$development || true) {
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
		return $this->_system->getWorkspace()->getActiveApplication();
	}

	/**
	 * Get alive applications created in the workspace.
	 * @return ApplicationsCollection
	 */
	public function getApplications(): ApplicationsCollection {
		return $this->_system->getWorkspace()->getApplications();
	}

	/**
	 *
	 * @return WorkSpace
	 */
	public function getWorkSpace(): WorkSpace {
		return $this->_system->getWorkspace();
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
		if ($this->checkNeccessary($object, true)) {
			$this->_notifications['create'][$object->getObjectID()] = $object;
		}
		
		return $this;
	}

	public function addUpdateNotification(VisualObject $object): self {
		if ($this->checkNeccessary($object)) {
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
	public function checkNeccessary(VisualObject $checkObject, bool $forCreation = false): bool {
		if ($checkObject->getObjectID() == $this->lastObjectID) {
			if ($this->ignoreUpdateObject) {
				return false;
			}
		}
		$notif = $this->getNotifications();

		// Verifico objetos a crear.
		if (!empty($notif['create'])) {
			foreach($notif['create'] as $object) {
				
				if ($checkObject->isDescendantOf($object)) {
					return false;
				}
				if ($object === $checkObject) {
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
				if ($object === $checkObject) {
					if ($forCreation) {
						return true;
					}
					return false;
				}

				/**
				 * Ahora verificamos que no haya algún contenedor en la lista de
				 * actualizaciones.
				 */
				if ($checkObject->isDescendantOf($object)) {
					return false;
				}

			}
		}

		return true;
	}
	
	public function purgeNotifications(): void {
		$update = $this->_notifications['update'];
		$create = $this->_notifications['create'];
		
		foreach($update as $x =>$updateObject) {
			foreach($create as $y => $createObject) {
				if ($createObject->isDescendantOf($updateObject)) {
					unset($create[$y]);
				}
			}
		}
		$this->_notifications['update'] = $update;
		$this->_notifications['create'] = $create;
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
					'objectId'       => $object->getObjectID(),
					'parentObjectId' => $parentObjectID,
					'content'        => '' . $object->render(),
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

	public function addNotification(string $name, array $data): void {
		$this->_notifications['general'][$name] = $data;
	}
	
	public function onCRUObjects(string $eventName, array $params): void {
		switch($eventName) {
			case 'createObject':
			case 'showObject':
				if (isset($params['object'])){
					$this->addCreateNotification($params['object']);
				}
			break;

			case 'removeObject':
			case 'hideObject':
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