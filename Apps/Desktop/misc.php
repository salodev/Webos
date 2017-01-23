<?php
namespace Webos\Apps\Desktop;
use \Webos\Visual\Control;
use \Webos\Visual\Container;
use \Webos\EventListener;
use \Webos\String;

class Window extends \Webos\Visual\Container {
	protected $_taskBar = null;

	public function initialize() {
		$this->_taskBar = $this->createObject(__NAMESPACE__ . '\TaskBar');
	}

	public function getTaskBar() {
		return $this->_taskBar;
	}

	public function getStarterMenu() {
		return $this->_starterMenu;
	}

	public function getAllowedActions() {}

	public function getAvailableEvents() {}
	
	public function render() {
		return $this->getTaskBar()->render();
	}
}

class StartButton extends Control {
	public function press() {
		$test = $this->getParentApp()->getObjectsByClassName(__NAMESPACE__ . '\StarterMenu');
		if (!$test->count()) {
			$menu = new StarterMenu($this->getParentApp());
		} else {
			foreach($test as $object) {
				$object->getParentApp()->removeChildObject($object);
			}
		}
	}

	public function getAllowedActions() {
		return array(
			'press',
		);
	}

	public function getAvailableEvents(){
		return array(
			'press',
		);
	}
	
	public function render() {
		$onclick = new String("__doAction('send',{actionName:'press', objectId:'__OBJECTID__'});return false;");
		$onclick->replace('__OBJECTID__', $this->getObjectID());

		//$html = new String('<input type="button" value="__VALUE__" onclick="__ONCLICK__" />');
		$html = new String('<a class="button starter-button" href="#" onclick="__ONCLICK__">__TEXT__</a>');
		$html->replace('__VALUE__',   $this->title);
		$html->replace('__ONCLICK__', $onclick);
		$html->replace('__TEXT__', 'Inicio');

		return $html;
	}

}

class TaskBar extends Control {
	private $_startButton = null;

	public function initialize() {

		$this->_startButton = $this->createObject(__NAMESPACE__ . '\StartButton', array(
			'title' => 'Inicio',
		));
	}

	public function getStartButton(){
		return $this->_startButton;
	}
	
	public function getTaskItems() {
		return $this->getObjectsByClassName(__NAMESPACE__ . '\TaskItem');
	}

	public function getAllowedActions() {
		return array();
	}

	public function getAvailableEvents() {
		return array();
	}
	
	public function render() {
		$html = 
			'<div id="' . $this->getObjectID() . '" class="task-bar-wrapper">' .
				'<div class="task-bar container">';

		$html .= $this->getStartButton()->render();
		$html .= '<div class="button separator"></div>';
		$html .= $this->getTaskItems()->render();
		$html .= 
				'</div>' .
				'<div class="shadow"></div>' .
			'</div>';
		return $html;
	}
	
}

class TaskItem extends Control {

	private $_linkedWindow = null;

	public function getAllowedActions() {
		return array(
			'press',
		);
	}

	public function getAvailableEvents() {
		return array(
			'press',
		);
	}

	public function press() {
		if (!$this->_linkedWindow) return false;

		/**
		 * @todo: Revisar esto. Camino "hablando con extaños"...
		 * hacer método getWorkSpace() a los VisualObjects sería una solución?
		 **/
		$this->getParentApp()->getWorkSpace()->setActiveApplication($this->_linkedWindow->getParentApp());
		$this->_linkedWindow->getParentApp()->setActiveWindow($this->_linkedWindow);
	}

	public function setLinkedWindow(\Webos\Visual\Window $window) {
		$this->_linkedWindow = $window;
	}

	public function getLinkedWindow() {
		return $this->_linkedWindow;
	}
	
	public function render() {
		$onclick = new String("__doAction('send',{actionName:'press', objectId:'__OBJECTID__'});return false;");
		$onclick->replace('__OBJECTID__', $this->getObjectID());
		
		//$html = new String('<input type="button" value="__VALUE__" onclick="__ONCLICK__" />');
		$html = new String('<a class="button task-button" href="#" onclick="__ONCLICK__">__VALUE__</a>');
		$html->replace('__VALUE__',   $this->title);
		$html->replace('__ONCLICK__', $onclick);

		return $html;
	}

}

class TaskItemFactory {
	public function createObject(Application $app) {
		$taskItem = new TaskItem(array(
			'title'         => $app->name,
			'description'   => $app->name,
			'applicationID' => $app->getObjectID(),
		));
		$taskItem->setLinkedApplication($app);

		return $taskItem;
	}
}

class StarterMenu extends Container {
	private $_appList = null;
	private $_shortcutsWrapper = null;

	public function initialize() {
		$this->_appList = $this->createObject(__NAMESPACE__ . '\AppList');
		//$this->_shortcutsWrapper = $this->createObject('ShortcutsWrapper');
	}

	public function appList() {
		return $this->_appList;
	}

	public function shortcutsWrapper() {
		return $this->_shortcutsWrapper;
	}

	public function getAllowedActions() {
		return array();
	}

	public function getAvailableEvents() {
		return array();
	}
	
	public function render() {
		$table = new String(
			'<table id="__id__" width="100%" tableborder="1px">' .
				'<tr><td width="250px">__applist__</td></tr>' .
				'<tr><td>__shortcuts__</td></tr>' .
			'</table>'
		);

		$table->replace('__id__',        $this->getObjectID());
		$table->replace('__applist__',   $this->appList()->render());
		$table->replace('__shortcuts__', '' /*$this->renderObject($object->shortCuts())*/);

		return $table;
	}
	
}

class AppList extends Control {

	public function initialize() {
		$this->refresh();
	}

	public function buttons() {
		return $this->_childObjects;
	}

	public function refresh() {

		$applications = array(
			'\WX\Application'          => 'Administracion WideExchange',
			'\Facturacion\Application' => 'Sistema de Facturacion',
			'\Webos\TaskManager'       => 'Administrador de aplicaciones.',
			'\Webos\MySQLBrowser'      => 'Acceder a bases de datos MySQL',
		);

		/*$press = new EventListener(array(
			'path'=> $this->getParentApp()->getConfig('eventsPath') . 'StarterMenu/AppList/appItemButton.press.php',
		));*/

		$this->buttons()->clear();

		foreach($applications as $name => $title){
			$button = $this->createObject(__NAMESPACE__ . '\StartOptionMenu', array(
				'value'   => $title,
				'title'   => $title,
				'appName' => $name,
				'actionName' => 'press',
			));

			$button->bind('press', new StartOptionMenuPressEventListener(array(
				'appName' => $name,
				'params'  => array(),
			)));
		}

		/* Botón Suspender sesión */ {
			$suspend = $this->createObject(__NAMESPACE__ . '\StartOptionMenu', array(
				'value' => 'Suspender Sesión',
				'title' => 'Suspender Sesión',
				'actionName' => 'press',
			));

			$suspend->bind('press', new EventListener(array(
				'path'=> $this->getParentApp()->getConfig('eventsPath') .
					'StarterMenu/AppList/suspend.press.php',
			)));
		}

		/* Botón Cerrar sesión */ {
			$close = $this->createObject(__NAMESPACE__ . '\StartOptionMenu', array(
				'value' => 'Cerrar Sesión',
				'title' => 'Cerrar Sesión',
				'actionName' => 'press',
			));

			$close->bind('press', new EventListener(array(
				'path'=> $this->getParentApp()->getConfig('eventsPath') .
					'StarterMenu/AppList/close.press.php',
			)));
		}
	}

	public function getAllowedActions() {
		return array();
	}

	public function getAvailableEvents() {
		return array();
	}
	
	public function render() {
		$wrapper = new String('<div class="AppList">__list__</div>');
		$list = $this->buttons()->render();
		$wrapper->replace('__list__', $list);

		return $wrapper;
	}
}

class ShortcutsWrapper extends Control {

	public function getAllowedActions() {
		return array();
	}

	public function getAvailableEvents() {
		return array();
	}
}

class StartOptionMenu extends Control {
	public function press() {
		$this->triggerEvent('press');
	}

	public function getAllowedActions() {
		return array(
			'press',
		);
	}

	public function getAvailableEvents(){
		return array(
			'press',
		);
	}
	
	public function render() {
		$html = new String(
			'<a class="StartOptionMenu"  onclick="__onclick__" href="#">__value__</a>'
		);

		$onclick = new String("__doAction('send',{actionName:'__actionName__', objectId:'__objectId__'});return false;");
		$onclick
			->replace('__actionName__', $this->actionName)
			->replace('__objectId__', $this->getObjectID());

		$html
			->replace('__value__',   $this->value)
			->replace('__onclick__', $onclick);

		return $html;
	}

}

class StartOptionMenuPressEventListener extends \Webos\EventListener {
	public function  execute($source, $eventName, $params) {
		$appName  = $this->_data['appName'];
		$appParams = $this->_data['params'];
		$source->getParentApp()->getWorkSpace()->startApplication($appName, $appParams);
	}
}