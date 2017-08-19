<?php

namespace Webos\Service;
use Exception;
use salodev\Thread;
use salodev\Implementations\SimpleServer;
use salodev\Debug\ObjectInspector;
use Webos\WorkSpace;
use Webos\SystemInterface;
use Webos\WorkSpaceHandlers\Instance as InstanceHandler;
use Webos\FrontEnd\Page;

class Server2 {
	
	/**
	 *
	 * @var string 
	 */
	static private $_token = null;
	
	/**
	 *
	 * @var \Webos\System
	 */
	static public $system = null;
	
	/**
	 *
	 * @var \Webos\SystemInterface;
	 */
	static public $interface = null;
	
	/**
	 *
	 * @var string;
	 */
	static private $_username = null;
	
	/**
	 *
	 * @var array 
	 */
	static private $_actionHandlers = [];
	
	static public function Prepare(string $username) {
		self::$_username = $username;
		self::$interface = new SystemInterface();
		self::$system = self::$interface->getSystemInstance();
		
		self::$system->setWorkSpaceHandler(new InstanceHandler(self::$system));
		
		self::$system->loadWorkSpace($username);
	}
	
	static public function RegisterActionHandlers() {
		self::RegisterActionHandler('startApplication', function(array $data) {
			if (empty($data['name'])) {
				throw new Exception('Missing name param');
			}
			$name   = $data['name'  ];
			$params = $data['params'] ?? [];
			self::GetWorkSpace()->startApplication($name, $params);
		});
		
		self::RegisterActionHandler('renderAll', function(array $data) {
			$html = self::GetWorkSpace()->getApplications()->getVisualObjects()->render();
			$page = new Page();
			$page->setContent($html);
			return $page->getHTML();
		});
		
		self::RegisterActionHandler('action', function(array $data) {
			$actionName = $data['name'      ];
			$objectID   = $data['objectID'  ];
			$parameters = $data['parameters'] ?? [];
			$ignoreUpdateObject = $data['ignoreUpdateObject'] ?? false;
			try {
				self::$interface->action($actionName, $objectID, $parameters, $ignoreUpdateObject);

			} catch (\Webos\Exceptions\Base $e) {
				$app = self::$interface->getActiveApplication();
				$app->openMessageWindow('Opps', $e->getMessage());
			} catch (\SG\Exception $e) {
				$app = self::$interface->getActiveApplication();
				$app->openMessageWindow('Opps', $e->getMessage());
			} catch (Exception $e) {
				$app = self::$interface->getActiveApplication();
				if (ENV==ENV_DEV) {
					$app->openExceptionWindow($e);
				} else {
					$app->openMessageWindow('Opps', $e->getMessage());
				}
			}
			
			$notif = self::$interface->getNotifications();
			
			// Notificaciones: Actualización.
			/*if (!empty($notif['update_stacks'])){
				$eventData = array();
				foreach($notif['update_stacks'] as $info) {
					$response['events'][] = array(
						'name' => 'updateElements_staks',
						'data' => $info,
					);
				}
			}*/
			$response = [];
			
			if (count($notif['update'])){
				$eventData = array();
				foreach($notif['update'] as $object) {
					$content = '';
					try {
						$content = $object->render();
					} catch (\Webos\Exceptions\Collection\NotFound $ex) {

					}
					$eventData[] = array(
						'objectId' => $object->getObjectID(),
						'content' => $content,
					);
				}

				$response['events'][] = array(
					'name' => 'updateElements',
					'data' => $eventData,
				);
			}

			// Notificaciones: Creación.
			if (count($notif['create'])){
				$eventData = array();
				foreach($notif['create'] as $object) {
					$parentObjectID = $object->hasParent() ? $object->getParent()->getObjectID() : '';
					$eventData[] = array(
						'parentObjectId' => $parentObjectID,
						'content' => '' . $object->render(),
					);
				}

				$response['events'][] = array(
					'name' => 'createElements',
					'data' => $eventData,
				);
			}

			// Notificaciones: Eliminación.
			if (count($notif['remove'])) {
				$eventData = array();
				foreach($notif['remove'] as $objectId) {
					$eventData[]['objectId'] = $objectId;
				}

				$response['events'][] = array(
					'name' => 'removeElements',
					'data' => $eventData,
				);
			}
			return $response;
		});
		
		self::RegisterActionHandler('setToken', function(array $data) {
			if (self::$_token) {
				throw new Exception('Token cant be modified');
			}
			if (empty($data['token'])) {
				throw new Exception('Missing token data');
			}
			self::$_token = $data['token'];
		});
		
		self::RegisterActionHandler('debug', function(array $data) {
			return ObjectInspector::inspect(self::$interface, $data['path'], true);
		});
	}
	
	static public function RegisterActionHandler($name, $handler) {
		self::$_actionHandlers[$name] = $handler;
	}
	
	static public function GetWorkSpace(): WorkSpace {
		return self::$system->getWorkSpace();
	}
	
	static public function Call($name, $token, array $data = [])  {
		if (self::$_token) {
			if (!$token) {
				throw new Exception('Missing token');
			}
			if (self::$_token != $token) {
				throw new Exception('Invalid token');
			}
		}
		if (!isset(self::$_actionHandlers[$name])) {
			throw new Exception("Undefined '{$name}' action handler");
		}
		$actionHandler = self::$_actionHandlers[$name];
		return $actionHandler($data);
	}
	
	static public function Listen($address, $port, $username) {
		self::Prepare($username);
		self::RegisterActionHandlers();
		/*Thread::SetSignalHandler([SIGINT,SIGTERM], function() {
			echo "señal recibida\n\n";
			die();
		});*/
		SimpleServer::Listen($address, $port, function($reqString) {
			$json = json_decode($reqString, true);
			if ($json==null) {
				return 'Bad json format: ' . $reqString;
			}

			$command  = $json['command'];
			$data     = $json['data'];
			$token    = $json['token'] ?? null;
			
			try {
				$commandResponse = self::Call($command, $token, $data);
			} catch(Exception $e) {
				return json_encode(array(
					'status' => 'error',
					'errorMsg' => $e->getMessage(),
				));
			}

			// echo "enviando: " . print_r($commandResponse, true);
			return json_encode(array(
				'status' => 'ok',
				'data'   => $commandResponse,
			));
		});
	}
}