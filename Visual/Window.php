<?php
namespace Webos\Visual;
class Window extends Container {
	protected $topControl        = 15;
	protected $leftControl       = 0;
	protected $widthLabelControl = 75;
	protected $widthFieldControl = 75;
	protected $showTitleControls = true;
	protected $allowClose        = true;
	protected $allowMaximize     = true;
	protected $controlProperties = array();
	protected $controlClassName  = '\Webos\Visual\Controls\TextField';
	protected $activeControl     = null;
	public $windowStatus = 'normal';
	
	public function preInitialize() {
		$this->title = $this->getObjectID();
		$this->width = '600px';
		$this->height = '400px';
		$this->top = '100px';
		$this->left = '100px';
	}
	
	public function initialize() {}

	public function  getHTMLRendererName() {
		return 'HTMLRendererWindowObject';
	}

	public function controls() {
		return $this->_childObjects;
	}

	public function getAvailableEvents(){
		return array(
			'close',
			'ready',
			'focus',
		);
	}

	public function getAllowedActions() {
		return array(
			'move',
			'resize',
			'close',
			'minimize',
			'maximize',
			'restore',
			'focus',
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
		$id = $this->getObjectID();

		if ($this->triggerEvent('close')) {
			$this->getParentApp()->closeWindow($this);
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
		$ws   = $this->getParentApp()->getWorkSpace();
		$app  = $ws->getActiveApplication();
		$test = $app->getObjectByID($this->getObjectID());

		if ($test instanceof \Webos\Visual\Window) {
			if ($test->active) {
				return true;
			}
		}

		return false;
	}

	protected function setControlProperties(array $properties = array()) {
		$this->controlProperties = $properties;
	}

	protected function clearControlProperties() {
		$this->controlProperties = array();
	}
	
	public function createControl($label, $className = '\Webos\Visual\Controls\TextField', array $options = array(), $attachToContainer = true) {
		if (!empty($options['top'])) {
			$this->topControl = $options['top'];
		}
		if (!empty($options['left'])) {
			$this->leftControl = $options['left'];
		}
		if (!empty($options['width'])) {
			$this->widthFieldControl = $options['width'];
		}
		if (!empty($options['labelWidth'])) {
			$this->widthLabelControl = $options['labelWidth'];
		}
		$this->createObject('\Webos\Visual\Controls\Label', array_merge(array(
			'left'  => $this->leftControl       . 'px',
			'width' => $this->widthLabelControl . 'px',
			'top'   => $this->topControl        . 'px',
		), $options, $this->controlProperties, array('text'=>$label)));

		$control = $this->createObject($className, array_merge(array(
			'top'   => $this->topControl            . 'px',
			'left'  => $this->widthLabelControl + 5 . 'px',
			'width' => $this->widthFieldControl . 'px',
			// 'name'  => $name,
		), $options, $this->controlProperties));
		$this->topControl +=28;

		/*if ($attachToContainer) {
			$this->$name = $control;
		}*/

		return $control;
	}
	
	/**
	 * 
	 * @param type $label
	 * @param type $options
	 * @return \Webos\Visual\Controls\TextBox
	 */
	public function createTextBox($label, $options = array()) {
		return $this->createControl($label, '\Webos\Visual\Controls\TextBox', $options);
	}
	
	/**
	 * 
	 * @param type $label
	 * @param type $options
	 * @return \Webos\Visual\Controls\TextBox
	 */
	public function createLabel($text, $options = array()) {
		return $this->createObject('\Webos\Visual\Controls\Label', array_merge($options, array('text'=>$text)));
	}
	
	/**
	 * 
	 * @param type $label
	 * @param type $options
	 * @return \Webos\Visual\Controls\ComboBox
	 */
	public function createComboBox($label, array $options = array()) {
		return $this->createControl($label, '\Webos\Visual\Controls\ComboBox', $options);
	}
	
	public function createButton($label, array $options = array()) {
		return $this->createObject('\Webos\Visual\Controls\Button', array_merge($options, ['value'=>$label]));
	}
	
	/**
	 * @return \Webos\Visual\Controls\ToolBar
	 */
	public function createToolBar() {
		$this->topControl += 20;
		return $this->createObject('\Webos\Visual\Controls\ToolBar');
	}
	
	public function createMenuBar() {
		return $this->createObject('\Webos\Visual\Controls\Menu\Bar');
	}
	
	/**
	 * 
	 * @param type $options
	 * @return \Webos\Visual\Controls\DataTable
	 */
	public function createDataTable($options = array()) {
		$initialOptions = array(
			'top' => $this->topControl . 'px',
			'left' => '0',
			'right' => '0',
			'bottom' => '0',
		);
		$options = array_merge($initialOptions, $options);
		return $this->createObject('\Webos\Visual\Controls\DataTable', $options);
	}

	public function addHorizontalButton($caption, $width = 80, array $params = array()) {
		$left = $this->__leftButton/1;
		$width = empty($params['width']) ? $width : $params['width'];
		$button = $this->createObject('\Webos\Visual\Controls\Button', array(
			'top'   => $this->topControl . 'px',
			'left'  => $left . 'px',
			'width' => $width,
			'value' => $caption,
		));
		$this->__leftButton = ($this->__leftButton/1) + 10 + ($width*1); // + ($width/1) + 10;
		return $button;
	}

	public function setFormData(array $data) {
		if (array_key_exists(0, $data)) {
			$this->_formData = $data[0];
		} else {
			$this->_formData = $data;
		}
		foreach($this->_formData as $field => $value) {
			$objects = $this->getChildObjects();
			foreach($objects as $childObject){
				if ($childObject->name == $field) {
					$childObject->value = $value;
				}
			}
		}
	}

	public function __set_active($value) {
		if ($value) {
			$this->getParentApp()->setActiveWindow($this);
		} else {
			if ($this->active) {
				$this->getParentApp()->setActiveWindow(null);
			}
		}
	}

	public function __get_active() {
		$activeWindow = $this->getParentApp()->getActiveWindow();
		if ($activeWindow instanceof Window) {
			if ($activeWindow->getObjectID() == $this->getObjectID()) {
				return true;
			}
		}
		
		return false;
	}
	
	public function render() {
		$html = $this->_getRenderTemplate();
		$content = $this->getChildObjects()->render();
		$html->replace('__CONTENT__', $content);
		return $html;
	}
	
	/**
	 * 
	 * @return \Webos\String
	 */
	protected function _getRenderTemplate() {
		$html = new \Webos\String(
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
				'__AUTOFOCUS__' .
			'</div>'
		);
		
		$autofocus = '';
		$activeControl = $this->getActiveControl();
		if ($activeControl instanceof \Webos\Visual\Control) {
			$autofocus = new \Webos\String(
				'<script>' .
					'$(function() {' .
						'$(\'#' . $activeControl->getObjectID() .'\').focus();' .
					'});' .
				'</script>'
			);
					
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
		));

		return $html;
	}
}
