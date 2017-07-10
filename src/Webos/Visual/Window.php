<?php
namespace Webos\Visual;
class Window extends Container {
	use FormContainer;

	protected $allowClose    = true;
	protected $allowMaximize = true;
	protected $activeControl = null;
	public $windowStatus = 'normal';
	
	public function bind(string $eventName, $eventListener, bool $persistent = true) {
		if ($eventName=='ready') { $persistent = false; }
		return parent::bind($eventName, $eventListener, $persistent);
	}
	
	public function preInitialize() {
		$this->title  = $this->getObjectID();
		$this->width  = 600;
		$this->height = 400;
		$this->top    = 100;
		$this->left   = 100;
	}
	
	public function initialize() {}

	public function controls() {
		return $this->_childObjects;
	}

	public function getAvailableEvents(): array {
		return array(
			'click',
			'close',
			'ready',
			'focus',
		);
	}

	public function getAllowedActions(): array {
		return array(
			'move',
			'resize',
			'close',
			'minimize',
			'maximize',
			'restore',
			'focus',
			'ready',
		);
	}
	
	public function getActiveControl() {
		return $this->activeControl;
	}
	
	public function setActiveControl(\Webos\Visual\Control $object) {
		$this->activeControl = $object;
	}
	
	public function hasFocus(\Webos\Visual\Control $object) {
		if ($this->activeControl === $object) {
			return true;
		}
	}

	public function resize($params) {
		$this->top  = $params['y1'];
		$this->left = $params['x1'];
		$this->width  = $params['x2'] - $params['x1'];
		$this->height = $params['y2'] - $params['y1'];
	}

	public function move(array $params) {
		$this->top  = $params['y'];
		$this->left = $params['x'];
	}

	public function close() {
		if ($this->triggerEvent('close')) {
			$this->getApplication()->closeWindow($this);
		}
	}

	public function maximize() {
		$this->status = 'maximized';
	}
	public function restore() {
		$this->status = '';
	}

	public function ready() {
		$this->triggerEvent('ready');
	}

	public function focus() {
		$this->triggerEvent('focus');
	}
	
	public function isActive() {
		$ws   = $this->getApplication()->getWorkSpace();
		$app  = $ws->getActiveApplication();
		$test = $app->getObjectByID($this->getObjectID());

		if ($test instanceof \Webos\Visual\Window) {
			if ($test->active) {
				return true;
			}
		}

		return false;
	}

	public function __set_active($value) {
		if ($value) {
			$this->getApplication()->setActiveWindow($this);
		} else {
			if ($this->active) {
				$this->getApplication()->setActiveWindow(null);
			}
		}
	}

	public function __get_active() {
		$activeWindow = $this->getApplication()->getActiveWindow();
		if ($activeWindow instanceof Window) {
			if ($activeWindow === $this) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * 
	 * @param string $className
	 * @param array $params
	 * @return Window;
	 */
	public function openWindow(string $className, array $params = array()): Window {
		return $this->getApplication()->openWindow($className, $params, $this);
	}
	
	/**
	 * 
	 * @param string $message
	 * @param string $title
	 * @return Window
	 */
	public function messageWindow(string $message, string $title = 'Message'): Windows\Message {
		return $this->openWindow(Windows\Message::class, [
			'title'   => $title,
			'message' => $message,
			'type'    => 'info',
		]);
	}
	
	/**
	 * 
	 * @param string $text
	 * @param callable $onConfirmCallback
	 * @return Window
	 */
	public function onConfirm(string $text, callable $onConfirmCallback): Windows\Confirm {
		return $this->openWindow(Windows\Confirm::class, [
			'message'=>$text
		])->bind('confirm', $onConfirmCallback);
	}
	
	/**
	 * 
	 * @param type $text
	 * @param \Webos\Visual\callable $onConfirmCallback
	 * @return Window
	 */
	public function onPrompt(string $text, callable $onConfirmCallback): Windows\Prompt {
		return $this->openWindow(Windows\Prompt::class, [
			'message'=>$text
		])->bind('confirm', $onConfirmCallback);
	}
	
	/**
	 * 
	 * @param string $text
	 * @param callable $onCloseCallback
	 * @return Windows\Message
	 */
	public function onMessageWindow(string $text, callable $onCloseCallback): Windows\Message {
		return $this->messageWindow($text, 'Message')->bind('close', $onCloseCallback);
	}
	
	public function render(): string {
		$html = $this->_getRenderTemplate();
		$content = $this->getChildObjects()->render();
		$html->replace('__CONTENT__', $content);
		return $html;
	}
	
	/**
	 * 
	 * @return \Webos\StringChar
	 */
	protected function _getRenderTemplate() {
		$html = new \Webos\StringChar(
			'<div id="__ID__" class="Window form-wrapper__ACTIVE____STATUS__" style="__STYLE__">' .
				'<div class="form-titlebar">' .
					'<div class="title">__TITLE__</div>' .
					'<div class="controls">' .
						'<a class="small-control restore" href="#" onclick="__doAction(\'send\', {actionName:\'restore\',objectId:\'__ID__\'});return false;"></a>' .
						'<a class="small-control maximize" href="#" onclick="__doAction(\'send\', {actionName:\'maximize\',objectId:\'__ID__\'});return false;"></a>' .
						'<a class="small-control close" href="#" onclick="__doAction(\'send\', {actionName:\'close\',objectId:\'__ID__\'});return false;"></a>' .
					'</div>' .
				'</div>' .
				'<div class="form-content">__CONTENT__</div>' .
				'__AUTOFOCUS__' . '__READY__' .
			'</div>'
		);
		
		$autofocus = '';
		$activeControl = $this->getActiveControl();
		if ($activeControl instanceof \Webos\Visual\Control) {
			$autofocus = new \Webos\StringChar(
				'<script>' .
					'$(function() {' .
						'$(\'#' . $activeControl->getObjectID() .'\').focus();' .
					'});' .
				'</script>'
			);
					
		}
		
		$ready = '';
		
		if ($this->_eventsHandler->hasListenersForEventName('ready')) {
			$ready = new \Webos\StringChar(
				'<script>' .
					'$(function() {' .
						'__doAction(\'send\', {actionName:\'ready\',objectId:\''. $this->getObjectID() . '\'});' .
					'});' .
				'</script>'
			);
			// $ready = '___doAction(\'send\', {actionName:\'ready\',objectId:\''. $this->getObjectID() . '\'});';
		}
		

		$styles = array(
			'width'    => $this->width,
			'height'   => $this->height,
			'top'      => $this->top,
			'left'     => $this->left,
			'position' => 'absolute',
		);

		$active = ($this->isActive()) ? ' active' : '';
		$status = ($this->windowStatus) ? ' ' . $this->windowStatus : '';

		if ($this->windowStatus == 'maximized') {
			unset($styles['width'], $styles['height']);
		}
		
		$html->replaces(array(
			'__ID__'        => $this->getObjectID(),
			'__ACTIVE__'    => $active,
			'__STATUS__'    => $status,
			'__TITLE__'     => $this->title,
			'__STYLE__'     => $this->getAsStyles($styles),
			'__AUTOFOCUS__' => $autofocus,
			'__READY__'     => $ready,
		));

		return $html;
	}
}
