<?php
namespace Webos;
use \salodev\Socket;
use \salodev\SocketServer;
use \salodev\Worker;
use \salodev\IO;

class Server {
	public $interface = null;
	public $system = null;
	public function __construct() {
		$this->interface = new Webos\SystemInterface();
		$this->system = $this->interface->getSystemInstance();
	}
	
	public function start($host = '127.0.0.1', $port = 3000) {
		$this->setupSocket($host, $port);
		$this->setupInteractiveConsole();
		SocketServer::Start();
	}
	
	public function setupSocket($host = '127.0.0.1', $port = 3000) {
		SocketServer::AddListener($host, $port, function(Socket $connection) {
			$connection->readLine(function($content) use ($connection){
				// IO::WriteLine("recibido: {$content}\n");
				// $connection->write('Escribiste: ' . $content);
				// \salodev\Timer::TimeOut(function() use ($content, $connection) {
				echo "recibido: {$content}\n";
				$json = json_decode($content, true);
				if ($json==null) {
					$connection->write('Bad json format: ' . $content);
					$connection->close();
					return;
				}

				$command = $json['command'];
				$data    = $json['data'];
				try {
					$commandResponse = $this->call($command, $data);
				} catch(Exception $e) {
					$connection->write(json_encode(array(
						'status' => 'error',
						'errorMsg' => $e->getMessage(),
					)));
					$connection->close();
					return;
				}
				
				if (!$commandResponse) {
					$connection->write(json_encode(array(
						'status' => 'error',
						'errorMsg' => 'Invalid response type',
					)));
				} else {
					echo "enviando: " . print_r($commandResponse, true);
					$connection->write(json_encode(array(
						'status' => 'ok',
						'data'   => $commandResponse,
					)));
					
				}
				$connection->close();
				// }, 5000);
			});
		});
	}
	
	public function setupInteractiveConsole() {
		IO::ReadLine(function($line) {
			if (empty($line)) {
				return;
			}
			$line = strtolower($line);
			if (in_array($line, ['exit','quit','stop'])) {
				Worker::Stop();
				return;
			}
			if ($line=='count') {
				IO::WriteLine(Worker::GetCountTasks());
				return;
			}
			if ($line=='mem') {
				IO::WriteLine(memory_get_usage());
				return;
			}
			if ($line=='list') {
				$rs = Worker::GetTasksList();
				IO::WriteLine('#   TASK DESCRIPTION                          PERSISTENT?');
				IO::WriteLine('---------------------------------------------------------');
				foreach($rs as $index => $row) {
					IO::WriteLine(sprintf("#%-3d %-40s %s", $index, $row['taskName'], $row['persistent']?'PERSISTENT':''));
				}
				return;
			}
			// if (in_array($line, ['h','help'])) {
				IO::WriteLine("COMMAND NAME   DESCRIPTION                        ");
				IO::WriteLine("--------------------------------------------------");
				IO::WriteLine("list           Show list of worker processes alive");
				IO::WriteLine("count          Show count of worker processes alive");
				IO::WriteLine("mem            Show Memory usage bytes");
				IO::WriteLine("h|help         This help");
				IO::WriteLine("exit|quit|stop Shut down the service");
			//}
		}, false);
	}
	
	public function call($command, array $data = array()) {
		if (!in_array($command, ['renderAll', 'action'])) {
			throw new Exception('Invalid command');
		}
		return $this->$command($data);
	}
	
	private function _loadWorkspace($username) {
		$this->system->addEventListener('createdWorkspace', function ($source, $eventName, $params) {
			$ws = $params['ws'];
			$ws->startApplication('SG\Application');
		});
		$ws = $this->system->loadCreateWorkSpace($username);
		if (!$ws instanceof Webos\Workspace) {
			throw new Exception('Error loading workspace');
		}
		return $ws;
	}
	
	public function renderAll(array $data) {
		if (empty($data['username'])) {
			throw new Exception('Empty username');
		}
		$username = $data['username'];
		$this->_loadWorkspace($username);
		$objects = $this->interface->getApplications()->getVisualObjects();

		$html = $objects->render();
		return $html;
	}
	
	public function action(array $data) {
		if (empty($data['username'])) {
			throw new Exception('Empty username');
		}
		$ws = $this->_loadWorkspace($data['username']);
		if (!$ws || !($ws instanceof Webos\WorkSpace)) {
			return array(
				'events' => array(
					array('name'=>'authUser'),
				)
			);
		}
		$response = array();
		$response['errors'] = array();
		// Si se envía un parámetro actionName, entonces se enviará la acción al sistema.
		if (!isset($data['actionName'])) {
			throw new Exception('Missing actionName param');
		}

		$params = array();

		foreach($data as $pname => $pvalue) {
			if ($pname != 'actionName' && $pname != 'objectId' && $pname != 'username'){
				$params[$pname] = $pvalue;
			}
		}

		try {
			$this->interface->action(
				$data['actionName'],
				$data['objectId'],
				$params
			);
		} catch (Exception $e) {
			$response['errors'][] = array(
				'message' => $e->getMessage(),
				'file'    => $e->getFile(),
				'line'    => $e->getLine(),
				'trace'   => $e->getTraceAsString(),
			);
		}

		// Obtengo las notificaciones.
		$notif = $this->interface->getNotifications();

		// Notificaciones: Actualización.
		if (count($notif['update'])){
			$eventData = array();
			foreach($notif['update'] as $object) {
				$eventData[] = array(
					'objectId' => $object->getObjectID(),
					'content' => '' . $object->render(),
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
				$parent = $object->getParent();
				$parentObjectId = ($parent instanceof \Webos\Application) ? '' :
						$parent->getObjectID();

				$eventData[] = array(
					'parentObjectId' => $parentObjectId,
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
	}
}