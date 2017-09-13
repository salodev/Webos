<?php
namespace Webos\Visual;
use Webos\Visual\Controls\Field;
use Webos\Visual\Controls\TextBox;
use Webos\Visual\Controls\PasswordBox;
use Webos\Visual\Controls\Label;
use Webos\Visual\Controls\Button;
use Webos\Visual\Controls\ComboBox;
use Webos\Visual\Controls\ToolBar;
use Webos\Visual\Controls\DataTable;
use Webos\Visual\Controls\Tree;
use Webos\Visual\Controls\Frame;
use Webos\Visual\Controls\Menu\Bar as MenuBar;
use Webos\Visual\Controls\MultiTab;

trait FormContainer {
	protected $maxTopControl     = 15;
	protected $topControl        = 15;
	protected $leftControl       = 0;
	protected $widthLabelControl = 75;
	protected $widthFieldControl = 75;
	protected $showTitleControls = true;
	protected $controlProperties = array();
	protected $controlClassName  = TextBox::class;
	protected $hasHorizontalButtons = false;
	protected $hasWindowButtons  = false;

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
	public function createControl(string $label, string $name, string $className = TextBox::class, array $options = array(), bool $attachToContainer = true): Control {
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
		$this->createObject(Label::class, array_merge(
			$options, 
			array('text'=>$label), 
			array(
				'text-align' => 'left',
				'top'   => $this->topControl,
				'left'  => $this->leftControl,
				'width' => $this->widthLabelControl,
			)
		));

		$control = $this->createObject($className, array_merge($options, array(
			'top'   => $this->topControl,
			'left'  => $this->leftControl + $this->widthLabelControl + 5,
			'width' => $this->widthFieldControl,
			'name'  => $name,
		)));
		$this->topControl += ($options['height'] ?? 23) + 5;
		if ($this->topControl > $this->maxTopControl) {
			$this->maxTopControl = $this->topControl;
		}

		if ($attachToContainer) {
			$this->$name = $control;
		}
		
		$this->fixWindowHeight();

		return $control;
	}
	
	/**
	 * 
	 * @param string $label
	 * @param string $name
	 * @param array $options
	 * @return \Webos\Visual\Controls\TextBox
	 */
	public function createTextBox(string $label, string $name, array $options = array()): TextBox {
		return $this->createControl($label, $name, TextBox::class, $options);
	}
	
	public function createPasswordBox(string $label, string $name, array $options = []): PasswordBox {
		return $this->createControl($label, $name, PasswordBox::class, $options);
	}
	
	/**
	 * 
	 * @param string $label
	 * @param string $name
	 * @param array $options
	 * @return \Webos\Visual\Controls\Label
	 */
	public function createLabelBox(string $label, string $name, array $options = array()): Label {
		return $this->createControl($label, $name, Label::class, $options);
	}
	
	/**
	 * 
	 * @param string $text
	 * @param array $options
	 * @return \Webos\Visual\Controls\Label
	 */
	public function createLabel(string $text, array $options = array()) {
		return $this->createObject(Label::class, array_merge($options, array('text'=>$text)));
	}
	
	/**
	 * 
	 * @param string $label
	 * @param string $name
	 * @param array $options
	 * @return \Webos\Visual\Controls\ComboBox
	 */
	public function createComboBox(string $label, string $name, array $options = array()): ComboBox {
		return $this->createControl($label, $name, ComboBox::class, $options);
	}
	
	/**
	 * 
	 * @param string $label
	 * @param array $options
	 * @return \Webos\Visual\Controls\Button
	 */
	public function createButton(string $label, array $options = array()): Button {
		return $this->createObject(Button::class, array_merge($options, ['value'=>$label]));
	}
	
	/**
	 * @return \Webos\Visual\Controls\ToolBar
	 */
	public function createToolBar(array $params = []): ToolBar {
		$this->topControl += 20;
		$this->maxTopControl += 20;
		$parentWindow = $this->getParentWindow();
		$parentWindow->height = $parentWindow->height + $this->topControl + 20 + 40;
		return $this->createObject(ToolBar::class, array_merge([
			'top' =>   0,
			'left' =>  0,
			'right' => 0,
		],$params));
	}
	
	public function createButtonsBar(): ToolBar {
		return $this->createToolBar([
			'fixedTo' => 'bottom',
			'horizontalAlign' => 'right',
		]);
	}
	
	/**
	 * 
	 * @return \Webos\Visual\Controls\Menu\Bar
	 */
	public function createMenuBar(): MenuBar {
		return $this->createObject(MenuBar::class);
	}
	
	/**
	 * 
	 * @param type $options
	 * @return \Webos\Visual\Controls\DataTable
	 */
	public function createDataTable(array $options = array()): DataTable {
		$initialOptions = array(
			'top' => $this->maxTopControl,
			'left' => $this->leftControl,
			'right' => '0',
			'bottom' => '0',
		);
		$options = array_merge($initialOptions, $options);
		$this->getParentWindow()->height = $this->topControl + ($options['height'] ?? 300) + 40;
		return $this->createObject(DataTable::class, $options);
	}
	
	/**
	 * 
	 * @param array $options
	 * @return Controls\Tree
	 */
	public function createTree(array $options = array()): Tree {
		$this->getParentWindow()->height = $this->topControl + ($options['height'] ?? 300) + 40;
		return $this->createObject(Tree::class, $options);
	}
	
	/**
	 * 
	 * @param array $options
	 * @return Controls\Frame
	 */
	public function createFrame(array $options = array()): Frame {
		return $this->createObject(Frame::class, $options);
	}
	
	public function createTabsFolder(): MultiTab {
		return $this->createObject(MultiTab::class);
	}

	/**
	 * 
	 * @param type $caption
	 * @param type $width
	 * @param array $params
	 * @return \Webos\Visual\Controls\Button
	 */
	public function addHorizontalButton($caption, $width = 80, array $params = array()): Button {
		if (!$this->hasHorizontalButtons) {
			$this->hasHorizontalButtons = true;
			$this->topHorizontalButtons = $this->maxTopControl + 10;
			$this->maxTopControl += 28 + 10;
			$this->topControl = $this->maxTopControl;
			$this->fixWindowHeight();
		}
		if (!empty($params['left'])) {
			$this->leftButton = $params['left']/1;
		}
		if (!empty($params['top'])) {
			$this->topControl = $this->topHorizontalButtons = $params['top']/1;
		}
		$left = $this->leftButton/1;
		$width = empty($params['width']) ? $width : $params['width'];
		$button = $this->createObject(Button::class, array_merge($params, array(
			'top'   => $this->topHorizontalButtons,
			'left'  => $left,
			'width' => $width,
			'value' => $caption,
		)));
		$this->leftButton = ($this->leftButton/1) + 10 + ($width*1); // + ($width/1) + 10;
		return $button;
	}
	
	public function createWindowButton($label, array $options = []): Button {
		if (!$this->hasWindowButtons) {
			$this->hasWindowButtons = true;
			$this->buttonsBar = $this->createButtonsBar();
		}
		return $this->buttonsBar->addButton($label, $options);
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
			if ($object instanceOf Field) {
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
			if ($object instanceOf Field) {
				$object->disabled = false;
			}
		};
	}
	
	public function disableForm() {
		$objects = $this->getChildObjects();
		foreach($objects as $object) {
			if ($object instanceOf Field) {
				$object->disabled = true;
			}
		};
	}
	
	public function fixWindowHeight() {
		$this->getParentWindow()->height = $this->maxTopControl + 45;
	}
}