<?php
namespace Webos\Visual;
trait FormContainer {
	protected $maxTopControl     = 15;
	protected $topControl        = 15;
	protected $leftControl       = 0;
	protected $widthLabelControl = 75;
	protected $widthFieldControl = 75;
	protected $showTitleControls = true;
	protected $controlProperties = array();
	protected $controlClassName  = '\Webos\Visual\Controls\TextField';
	protected $hasHorizontalButtons = false;

	protected function setControlProperties(array $properties = array()) {
		$this->controlProperties = $properties;
	}

	protected function clearControlProperties() {
		$this->controlProperties = array();
	}
	
	/**
	 * 
	 * @param string $label
	 * @param string $name
	 * @param string $className
	 * @param array $options
	 * @param bool $attachToContainer
	 * @return \Webos\Visual\Control
	 */
	public function createControl($label, $name, $className = '\Webos\Visual\Controls\TextField', array $options = array(), $attachToContainer = true) {
		if (isset($options['top'])) {
			$this->topControl = $options['top'];
		}
		if (isset($options['left'])) {
			$this->leftControl = $options['left'];
			$this->leftButton = $options['left'];
		}
		if (isset($options['width'])) {
			$this->widthFieldControl = $options['width'];
		}
		if (isset($options['labelWidth'])) {
			$this->widthLabelControl = $options['labelWidth'];
		}
		$this->createObject('\Webos\Visual\Controls\Label', array_merge(
			$options, 
			array('text'=>$label), array(
			'top'   => $this->topControl        . 'px',
			'left'  => $this->leftControl       . 'px',
			'width' => $this->widthLabelControl . 'px',
		)));

		$control = $this->createObject($className, array_merge($options, array(
			'top'   => $this->topControl . 'px',
			'left'  => $this->leftControl + $this->widthLabelControl + 5 . 'px',
			'width' => $this->widthFieldControl . 'px',
			'name'  => $name,
		)));
		$this->topControl +=28;
		if ($this->topControl > $this->maxTopControl) {
			$this->maxTopControl = $this->topControl;
		}

		if ($attachToContainer) {
			$this->$name = $control;
		}

		return $control;
	}
	
	/**
	 * 
	 * @param string $label
	 * @param string $name
	 * @param array $options
	 * @return \Webos\Visual\Controls\TextBox
	 */
	public function createTextBox($label, $name, array $options = array()) {
		return $this->createControl($label, $name, '\Webos\Visual\Controls\TextBox', $options);
	}
	
	/**
	 * 
	 * @param string $label
	 * @param string $name
	 * @param array $options
	 * @return \Webos\Visual\Controls\TextBox
	 */
	public function createLabelBox($label, $name, array $options = array()) {
		return $this->createControl($label, $name, '\Webos\Visual\Controls\Label', $options);
	}
	
	/**
	 * 
	 * @param string $text
	 * @param array $options
	 * @return \Webos\Visual\Controls\TextBox
	 */
	public function createLabel($text, array $options = array()) {
		return $this->createObject('\Webos\Visual\Controls\Label', array_merge($options, array('text'=>$text)));
	}
	
	/**
	 * 
	 * @param string $label
	 * @param string $name
	 * @param array $options
	 * @return \Webos\Visual\Controls\ComboBox
	 */
	public function createComboBox($label, $name, array $options = array()) {
		return $this->createControl($label, $name, '\Webos\Visual\Controls\ComboBox', $options);
	}
	
	/**
	 * 
	 * @param string $label
	 * @param array $options
	 * @return \Webos\Visual\Controls\Button
	 */
	public function createButton($label, array $options = array()) {
		return $this->createObject('\Webos\Visual\Controls\Button', array_merge($options, ['value'=>$label]));
	}
	
	/**
	 * @return \Webos\Visual\Controls\ToolBar
	 */
	public function createToolBar() {
		$this->topControl += 20;
		$this->maxTopControl += 20;
		return $this->createObject('\Webos\Visual\Controls\ToolBar');
	}
	
	/**
	 * 
	 * @return \Webos\Visual\Controls\Menu\Bar
	 */
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
			'top' => $this->maxTopControl . 'px',
			'left' => $this->leftControl . 'px',
			'right' => '0',
			'bottom' => '0',
		);
		$options = array_merge($initialOptions, $options);
		return $this->createObject('\Webos\Visual\Controls\DataTable', $options);
	}
	
	/**
	 * 
	 * @param array $options
	 * @return Controls\Tree
	 */
	public function createTree(array $options = array()) {
		return $this->createObject('\Webos\Visual\Controls\Tree', $options);
	}
	
	/**
	 * 
	 * @param array $options
	 * @return Controls\Frame
	 */
	public function createFrame(array $options = array()) {
		return $this->createObject('\Webos\Visual\Controls\Frame', $options);
	}

	public function addHorizontalButton($caption, $width = 80, array $params = array()) {
		if (!$this->hasHorizontalButtons) {
			$this->hasHorizontalButtons = true;
			$this->topHorizontalButtons = $this->maxTopControl;
			$this->maxTopControl += 28;
			$this->topControl = $this->maxTopControl;
		}
		if (!empty($params['left'])) {
			$this->leftButton = $params['left']/1;
		}
		$left = $this->leftButton/1;
		$width = empty($params['width']) ? $width : $params['width'];
		$button = $this->createObject('\Webos\Visual\Controls\Button', array_merge($params, array(
			'top'   => $this->topHorizontalButtons . 'px',
			'left'  => $left . 'px',
			'width' => $width . 'px',
			'value' => $caption,
		)));
		$this->leftButton = ($this->leftButton/1) + 10 + ($width*1); // + ($width/1) + 10;
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
	
	public function clearFormData() {
		$objects = $this->getChildObjects();
		foreach($objects as $object) {
			if ($object instanceOf Controls\Field) {
				$object->value = null;
			}
		};
	}
	
	/**
	 * @return array
	 */
	public function getFormData(array $merge = array()) {
		$formData = array();
		$objects = $this->getChildObjects();
		foreach($objects as $childObject){
			if ($childObject->name) {
				$formData[$childObject->name] = $childObject->value;
			}
		}
		return array_merge($formData, $merge);
	}
	
	public function enableForm() {
		$objects = $this->getChildObjects();
		foreach($objects as $object) {
			if ($object instanceOf Controls\Field) {
				$object->disabled = false;
			}
		};
	}
	
	public function disableForm() {
		$objects = $this->getChildObjects();
		foreach($objects as $object) {
			if ($object instanceOf Controls\Field) {
				$object->disabled = true;
			}
		};
	}
}