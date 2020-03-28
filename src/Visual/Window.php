<?php
namespace Webos\Visual;

use Webos\VisualObject;
use Webos\Visual\Windows\Wait;
use Webos\Visual\Windows\Message;
use Webos\Visual\Windows\Prompt;
use Webos\Visual\Windows\PasswordPrompt;
use Webos\Visual\Windows\Confirm;
use Webos\Visual\Windows\Question;
use Webos\Visual\Control;
use Webos\Exceptions\Collection\NotFound;
use Webos\StringChar;
use Webos\WorkSpace;

class Window extends Container {

	protected $allowClose    = true;
	protected $allowMaximize = true;
	protected $activeControl = null;
	public $windowStatus = 'normal';
	
	use KeysEvents;
	
	public function bind(string $eventName, $eventListener, bool $persistent = true, array $contextData = []): VisualObject {
		if ($eventName=='ready') { $persistent = false; }
		return parent::bind($eventName, $eventListener, $persistent, $contextData);
	}
	
	public function preInitialize(): void {
		$this->title        = $this->getObjectID();
		$this->width        = 600;
		$this->height       = 400;
		$this->top          = 100;
		$this->left         = 100;
		$this->showTitle    = true;
		$this->showControls = true;
		$this->allowResize  = true;
		$this->modal        = false;
		
		$this->onContextMenu(function($data) {
			$menu = $data['menu'];
			$menu->createItem('Cerrar')->onClick(function() {
				$this->close();
			});
		});
		
		$this->onKeyEscape(function() {
			$this->close();
		});
	}
	
	public function initialize(array $params = []) {}
	
	public function afterInitialize() {
		parent::afterInitialize();

		if ($this->getWorkspace()->isSmart()) {
			$this->top    = 0;
			$this->left   = 0;
			$this->bottom = 0;
			$this->height = null;
			if ($this->modal) {
				$this->right = 0;
				$this->width = null;
			}
		} else {
			$margin = 10;
			$h = $this->getWorkspace()->getViewportHeight();
			$w = $this->getWorkspace()->getViewportWidth();
		
		
			/**
			 * In order to keep window placed into screen so check dimensions and
			 * try to put the window better as possible.
			 * First check it for vertical position
			 */
			if ($this->top + $this->height > $h) {
				$this->top = $h - $this->height - $margin;
				if ($this->top < $margin) {
					$this->top = $margin;
				}
			}

			// Now for horizontal position.
			if ($this->left + $this->width > $w) {
				$this->left = $h - $this->width - $margin;
				if ($this->left < $margin) {
					$this->left = $margin;
				}
			}
		}
	}
	
	public function onContextMenu(callable $cb, bool $persistent = true, array $contextData = []): self {
		$this->bind('contextMenu', $cb, $persistent, $contextData);
		return $this;
	}
	
	public function getActiveControl(): Control {
		if (!($this->activeControl instanceof Control)) {
			throw new \Exception('No active control');
		}
		return $this->activeControl;
	}
	
	public function hasActiveControl(): bool {
		return $this->activeControl instanceof Control;
	}
	
	public function setActiveControl(Control $object): self {
		$this->activeControl = $object;
		return $this;
	}
	
	public function hasFocus(Control $object):bool {
		return $this->activeControl === $object;
	}

	public function close(bool $force = false): void {
		/**
		 * Force it anyway avoiding event handlers
		 */
		if ($force === true) {
			$this->getApplication()->closeWindow($this);
		}
		
		/**
		 * If trigger, so can be stopped if any handler
		 * returns false
		 */
		if ($this->triggerEvent('close')) {
			$this->getApplication()->closeWindow($this);
		}
	}

	public function action_resize(array $params = []): void {
		if (!($this->allowResize??true)) {
			return;
		}
		$this->top    = $params['y1'];
		$this->left   = $params['x1'];
		$this->width  = $params['x2'] - $params['x1'];
		$this->height = $params['y2'] - $params['y1'];
	}

	public function action_move(array $params = []): void {
		$this->top  = $params['y'];
		$this->left = $params['x'];
	}

	public function action_close(): void {
		$this->close();
	}

	public function action_maximize(): void {
		$this->status = 'maximized';
	}
	public function action_restore(): void {
		$this->status = '';
	}

	public function action_ready(): void {
		$this->triggerEvent('ready');
	}

	public function action_focus(): void {
		$this->triggerEvent('focus');
	}
	
	public function action_contextMenu(array $params = []) {
		if (empty($params['top']) || empty($params['left'])) {
			return;
		}
		if ($this->hasListenerFor('contextMenu')) {
			$menu = $this->getParentWindow()->createContextMenu($params['top'], $params['left']);
			$eventData = ['menu' => $menu];
			$this->triggerEvent('contextMenu', $eventData);
		}
	}
	
	public function isActive(): bool {
		$ws   = $this->getApplication()->getWorkSpace();
		$app  = $ws->getActiveApplication();
		try {
			$test = $app->getObjectByID($this->getObjectID());
		} catch(NotFound $e) {
			return false;
		}

		if ($test instanceof self) {
			if ($test->active) {
				return true;
			}
		}

		return false;
	}

	public function __set_active(bool $value): void {
		if ($value) {
			$this->getApplication()->setActiveWindow($this);
		} else {
			if ($this->active) {
				$this->getApplication()->setActiveWindow(null);
			}
		}
	}

	public function __get_active(): bool {
		$activeWindow = $this->getApplication()->getActiveWindow();
		if ($activeWindow instanceof Window) {
			if ($activeWindow === $this) {
				return true;
			}
		}
		
		return false;
	}
	
	public function __set_relativeTo(Window $relativeTo): void {
		if (!$this->getWorkSpace()->isSmart() && !$relativeTo->_embed) {
			$this->top  = $relativeTo->top  + 100;
			$this->left = $relativeTo->left + 100;
		}
	}
	
	public function getWorkspace(): WorkSpace {
		return $this->getParentApp()->getWorkSpace();
	}
	
	public function modal(bool $value = true): self {
		$this->modal = $value;
		return $this;
	}
	
	/**
	 * 
	 * @param string $className
	 * @param array $params
	 * @return Window;
	 */
	public function openWindow(string $className = null, array $params = []): Window {
		return $this->getApplication()->openWindow($className, $params, $this)->syncDataWith($this);
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
	
	public function questionWindow(string $questionMessage, string $title = 'Question'): Question {
		return $this->openWindow(Question::class, [
			'title'   => $title,
			'message' => $questionMessage,
		]);
	}
	
	public function promptWindow(string $message, string $defaultValue = null): Prompt {
		return $this->getParentApp()->openPromptWindow($message, $defaultValue);
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
	 * @param type $text
	 * @param \Webos\Visual\callable $onConfirmCallback
	 * @return Window
	 */
	public function onPasswordPrompt(string $text, callable $onConfirmCallback, string $defaultValue = null): PasswordPrompt {
		return $this->openWindow(PasswordPrompt::class, [
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
	
	public function onReady(callable $cb, bool $persistent = true, array $context = []): self {
		$this->bind('ready', $cb);
		return $this;
	}
	
	public function onOpen(callable $cb): self {
		$this->bind('open', $cb);
		return $this;
	}
	
	public function onClose(callable $cb): self {
		$this->bind('close', $cb);
		return $this;
	}
	
	public function newData(array $params = []): self {
		$this->triggerEvent('newData', $params);
		return $this;
	}
	
	public function syncDataWith(self $syncWith): self {
		$this->onNewData(function($context, $data) {
			$object = $context['syncWith'];
			$object->newData($data);
		}, true, ['syncWith' => $syncWith]);
		return $this;
	}
	
	public function onNewData(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('newData', $function, $persistent, $context);
		return $this;
	}
	
	public function navigateURL(string $url, array $data = []): self {
		$this->getParentApp()->triggerSystemEvent('navigateURL', $this, [
			'url' => $url,
			'data' => $data,
		]);
		
		return $this;
	}
	
	public function streamContent(string $content, string $mimetype = null, string $fileName = null): self {
		$this->getParentApp()->streamContent($content, $mimetype, $fileName);
		return $this;
	}
	
	public function streamFile(string $path): self {
		$this->getParentApp()->streamFile($path);
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
	protected function _getRenderTemplate(): StringChar {
		$keys = [];
		$directives = [
			'container-key-receiver',
			// 'resize',
			// 'focus',
		];
		foreach($this->_getKeysForEvents() as $keyName) {
			$eventName = "keyPress{$keyName}";
			if ($this->hasListenerFor($eventName)) {
				$keys[] = $keyName;
			}
		}

		if (count($keys)) {
			$keys = implode(',', array_unique($keys));
			$directives[] = "key-press=\"{$keys}\"";			
		}
		if ($this->hasListenerFor('ready')) {
			$directives[] = 'ready';
		}
		if ($this->allowResize && !$this->_embed) {
			$directives[] = 'resize';
		}
		
		$showModal = $this->modal && !$this->getParentApp()->getWorkSpace()->isSmart();
		
		$html = new StringChar(
			'<div ' .
				($showModal ? '' : 'id="__ID__" ') .
				'class="Window form-wrapper__ACTIVE____STATUS__" ' .
				'__STYLE__ __READY__ __DIRECTIVES__' .
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
		
		if ($showModal) {
			$html = new StringChar('<div webos no-action="close" bubble="false" id="__ID__" class="modal-wrapper">' . $html . '</div>');
		}
		
		if ($this->_embed) {
			$html = new StringChar('<div id="__ID__" __READY__ __DIRECTIVES__ style="top:0;left:0;bottom:0;right:0;position:absolute;overflow:hidden;">__CONTENT____AUTOFOCUS__</div>');
		}
		
		$autofocus = '';
		if ($this->hasActiveControl()) {
			$activeControl = $this->getActiveControl();
			$autofocus = new StringChar(
				'<script>' .
					'$(function() {' .
						'$(\'#' . $activeControl->getObjectID() .'\').focus();' .
					'});' .
				'</script>'
			);
					
		}
		
		$hasReadyListeners = $this->hasListenerFor('ready');
		
		$absolutize = true;
		if (empty($this->left) && $this->horizontalAlign && $this->horizontalAlign == 'center') {
			$this->position    = 'relative';
			$absolutize        = false;
			$this->marginLeft  = 'auto';
			$this->marginRight = 'auto';
		}

		$active = ($this->isActive()) ? ' active' : '';
		$status = ($this->windowStatus) ? ' ' . $this->windowStatus : '';

		if ($this->windowStatus == 'maximized') {
			// unset($styles['width'], $styles['height']);
		}
		
		$html->replaces([
			'__ID__'         => $this->getObjectID(),
			'__ACTIVE__'     => $active,
			'__STATUS__'     => $status,
			'__TITLE__'      => $this->title,
			'__STYLE__'      => $this->getInlineStyle($absolutize),
			'__AUTOFOCUS__'  => $autofocus,
			'__READY__'      => $hasReadyListeners ? 'webos ready': '',
			'__DIRECTIVES__' => count($directives) ? 'webos ' . implode(' ', $directives): '',
		]);

		return $html;
	}
}
