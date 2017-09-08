<?php
namespace Webos\Visual;
use Webos\VisualObject;
use Webos\Visual\Windows\Wait;
use Webos\Visual\Windows\Message;
use Webos\Visual\Windows\Prompt;
use Webos\Visual\Windows\Confirm;
use Webos\Visual\Controls\Menu\ListItems;
use Webos\Visual\Controls\Menu\Item;
use Webos\Exceptions\Collection\NotFound;
class Window extends Container {
	use FormContainer;

	protected $allowClose    = true;
	protected $allowMaximize = true;
	protected $activeControl = null;
	public $windowStatus = 'normal';
	
	public function bind(string $eventName, $eventListener, bool $persistent = true, array $contextData = []): VisualObject {
		if ($eventName=='ready') { $persistent = false; }
		return parent::bind($eventName, $eventListener, $persistent, $contextData);
	}
	
	public function preInitialize() {
		$this->title  = $this->getObjectID();
		$this->width  = 600;
		$this->height = 400;
		$this->top    = 100;
		$this->left   = 100;
		$this->showTitle    = true;
		$this->showControls = true;
		$this->allowResize  = true;
		
		$this->onContextMenu(function($data) {
			$menu = $data['menu'];
			$menu->createItem('Cerrar')->onClick(function() {
				$this->close();
			});
		});
	}
	
	public function initialize(array $params = []) {}

	public function controls() {
		return $this->_childObjects;
	}

	public function getAvailableEvents(): array {
		return array(
			'move',
			'click',
			'close',
			'ready',
			'focus',
			'contextMenu',
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
			'contextMenu',
		);
	}
	
	public function contextMenu($params) {
		if (empty($params['top']) || empty($params['left'])) {
			return;
		}
		if ($this->hasListenerFor('contextMenu')) {
			$menu = $this->getParentWindow()->createContextMenu($params['top'], $params['left']);
			$eventData = ['menu' => $menu];
			$this->triggerEvent('contextMenu', $eventData);
		}
	}
	
	public function onContextMenu(callable $cb, bool $persistent = true, array $contextData = []): self {
		$this->bind('contextMenu', $cb, $persistent, $contextData);
		return $this;
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
		if (!($this->allowResize??true)) {
			return;
		}
		$this->top    = $params['y1'];
		$this->left   = $params['x1'];
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
		try {
			$test = $app->getObjectByID($this->getObjectID());
		} catch(NotFound $e) {
			return false;
		}

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
	 * @param type $top
	 * @param type $left
	 * @return ListItems
	 */
	public function createContextMenu($top, $left): ListItems {

		$menu = $this->createObject(ListItems::class, [
			'top'      => $top,
			'left'     => $left,
			'position' => 'fixed',
		]);
		
		$this->getApplication()->addSystemEventListener('actionCalled', function($context) {
			$menu     = $context['menu'];
			$menu->getParentWindow()->removeChild($menu);
		}, false, ['menu'=>$menu]);
		
		return $menu;
	}
	
	/**
	 * 
	 * @param string $className
	 * @param array $params
	 * @return Window;
	 */
	public function openWindow(string $className = null, array $params = array()): Window {
		return $this->getApplication()->openWindow($className, $params, $this);
	}
	
	/**
	 * 
	 * @param string $message
	 * @param string $title
	 * @return Window
	 */
	public function messageWindow(string $message, string $title = 'Message'): Message {
		return $this->openWindow(Message::class, [
			'title'   => $title,
			'message' => $message,
			'type'    => 'info',
		]);
	}
	
	public function waitWindow(string $message, callable $callback): Wait {
		return $this->openWindow(Wait::class, [
			'message' => $message
		])->onReady($callback);
	}
	
	/**
	 * 
	 * @param string $text
	 * @param callable $onConfirmCallback
	 * @return Window
	 */
	public function onConfirm(string $text, callable $onConfirmCallback): Confirm {
		return $this->openWindow(Confirm::class, [
			'message'=>$text
		])->bind('confirm', $onConfirmCallback);
	}
	
	/**
	 * 
	 * @param type $text
	 * @param \Webos\Visual\callable $onConfirmCallback
	 * @return Window
	 */
	public function onPrompt(string $text, callable $onConfirmCallback, string $defaultValue = null): Prompt {
		return $this->openWindow(Prompt::class, [
			'message'      => $text,
			'defaultValue' => $defaultValue,
		])->bind('confirm', $onConfirmCallback);
	}
	
	/**
	 * 
	 * @param string $text
	 * @param callable $onCloseCallback
	 * @return Windows\Message
	 */
	public function onMessageWindow(string $text, callable $cb): Message {
		return $this->messageWindow($text, 'Message')->onClose($cb);
	}
	
	public function onReady(callable $cb): self {
		$this->bind('ready', $cb);
		return $this;
	}
	
	public function onClose(callable $cb): self {
		$this->bind('close', $cb);
		return $this;
	}
	
	public function render(): string {
		$html = $this->_getRenderTemplate();
		$content = $this->text ?? '';
		$content .= $this->getChildObjects()->render();
		$html->replace('__CONTENT__', $content);
		return $html;
	}
	
	/**
	 * 
	 * @return \Webos\StringChar
	 */
	protected function _getRenderTemplate() {
		$html = new \Webos\StringChar(
			'<div 
				id="__ID__" 
				class="Window form-wrapper__ACTIVE____STATUS__"
				style="__STYLE__"__READY__' . (($this->allowResize ?? true) ? ' webos resize' : '') .
			'>' .
				'<div class="form-titlebar">' .
					($this->showTitle ?
						'<div class="title" webos no-contextmenu="titleBar" move=".form-wrapper">__TITLE__</div>' .
						($this->showControls ?
							'<div class="controls">' .
								'<a class="small-control restore"  href="#" webos restore></a>' .
								'<a class="small-control maximize" href="#" webos maximize></a>' .
								'<a class="small-control close"    href="#" webos close></a>' .
							'</div>' : ''
						) : '' 
					) .
				'</div>' .
				'<div class="form-content">__CONTENT__</div>' .
				'__AUTOFOCUS__' . 
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
		
		$hasReadyListeners = $this->_eventsHandler->hasListenersForEventName('ready');
		

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
			'__READY__'     => $hasReadyListeners ? 'webos ready': '',
		));

		return $html;
	}
}
