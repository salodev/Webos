<?php
namespace Webos\Apps;
class Desktop extends \Webos\Application {

	private $_deskWindow = null;

	public function main(array $params = array()) {
		$this->_deskWindow = new Desktop\Window($this);
		$this->setActiveWindow($this->_deskWindow);

		$this->addSystemEventListener('createObject', array($this, 'createWindowEventListener'));
		$this->addSystemEventListener('removeObject', array($this, 'removeWindowEventListener'));
	}

	public function createWindowEventListener($source, $eventName, $params) {
		
		if (!empty($params['object'])) {
			if ($params['object'] instanceof \Webos\Window) {
				Log::write('Entra en createWindowEventListener');
				$window = $params['object'];				
				
				$taskItem = $this->_deskWindow->getTaskBar()->createObject('\Webos\Apps\Desktop\TaskItem', array(
					'title' => $window->title,
				));

				$taskItem->setLinkedWindow($window);
			}
		}
	}

	public function removeWindowEventListener($source, $eventName, $params) {
		if (!empty($params['objectId'])) {
			$taskItems = $this->_deskWindow->getObjectsByClassName('\Webos\Apps\Desktop\TaskItem');
			foreach($taskItems as $taskItem) {
				if ($taskItem->getLinkedWindow()->getObjectID() == $params['objectId']) {
					$this->_deskWindow->getTaskBar()->getChildObjects()->removeObject($taskItem);
					break;
				}
			}
		}
	}

	public function getActiveApplication() {
		return $this->getWorkSpace()->getActiveApplication();
	}

	public function  getName() {
		return 'DesktopApplication for WebDesktop PHP Framework';
	}

	public function  getVersion() {
		return '1.0.0';
	}

	public function  getProvider() {
		return 'SaloWeb';
	}
}