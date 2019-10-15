<?php

namespace Webos\Visual;

use Webos\Exceptions\Collection\IsEmpty;
use Webos\VisualObject;
use Webos\Visual\Controls\Button;
use Webos\Visual\Controls\ComboBox;
use Webos\Visual\Controls\DataTable;
use Webos\Visual\Controls\Field;
use Webos\Visual\Controls\FilePicker;
use Webos\Visual\Controls\Frame;
use Webos\Visual\Controls\Group;
use Webos\Visual\Controls\HorizontalSeparator;
use Webos\Visual\Controls\HtmlContainer;
use Webos\Visual\Controls\Image;
use Webos\Visual\Controls\Label;
use Webos\Visual\Controls\Link;
use Webos\Visual\Controls\LinkButton;
use Webos\Visual\Controls\Menu\Bar as MenuBar;
use Webos\Visual\Controls\Menu\ListItems;
use Webos\Visual\Controls\MultiTab;
use Webos\Visual\Controls\PasswordBox;
use Webos\Visual\Controls\TextBox;
use Webos\Visual\Controls\CheckBox;
use Webos\Visual\Controls\ToolBar;
use Webos\Visual\Controls\Tree;
use Webos\Visual\Controls\VerticalSeparator;
use Webos\Visual\Window;

trait FormContainer {
	
	protected $maxTopControl     = 15;
	protected $hasWindowButtons  = false;
	
	protected function _getNextPositions(): array {
		$margin = 5;
		
		$positions = [
			'top'        => $margin,
			'left'       => 0,
			'height'     => 25,
			'width'      => 300,
			'labelWidth' => 100,
		];
		
		try {
			$lastChild               = $this->getLastChild();
			$positions['top'       ] = $lastChild->top  + $lastChild->height + $margin;
			$positions['left'      ] = $lastChild->left;
			$positions['height'    ] = $lastChild->height;
			$positions['width'     ] = $lastChild->width ?? 300;
			$positions['labelWidth'] = $lastChild->labelWidth;
		} catch (IsEmpty $e) {
			// discard $e;
		}
		
		return $positions;
	}
	
	protected function _getNextPosTop(): int {
		return $this->_getNextPositions()['top'];
	}
	
	protected function _getNextPosLeft(): int {
		return $this->_getNextPositions()['left'];
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
	public function createControl(string $label, string $name, string $className = TextBox::class, array $options = [], bool $attachToContainer = true): Control {
		$positions = $this->_getNextPositions();
		
		$group = $this->createObject(Group::class, array_merge([
			'top'        => $positions['top'       ],
			'left'       => $positions['left'      ],
			'width'      => $positions['width'     ],
			'height'     => $positions['height'    ],
			'labelWidth' => $positions['labelWidth'],
			
		], $options, [
			'label'      => $label,
			'className'  => $className,
			'name'       => $name,
			'textAlign'  => 'left',
		]));
		$control = $group->getControl();

		if ($attachToContainer) {
			$this->$name = $control;
		}
		$this->setMaxTopControl();

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
	public function createTextBox(string $label, string $name, array $options = []): TextBox {
		return $this->createControl($label, $name, TextBox::class, $options);
	}
	
	public function createPasswordBox(string $label, string $name, array $options = []): PasswordBox {
		return $this->createControl($label, $name, PasswordBox::class, $options);
	}
	
	public function createCheckBox(string $label, string $name, array $options = []): CheckBox {
		$list = $this->getObjectsByClassName(CheckBox::class);
		if ($list->count() > 0) {
			$lastCheck = $list->getLastObject();
			$options = array_merge([
				'checkedValue'   => $lastCheck->checkedValue,
				'uncheckedValue' => $lastCheck->uncheckedValue,
			], $options);
		}
		return $this->createControl($label, $name, CheckBox::class, $options);
	}


	/**
	 * 
	 * @param string $label
	 * @param string $name
	 * @param array $options
	 * @return \Webos\Visual\Controls\Label
	 */
	public function createLabelBox(string $label, string $name, array $options = []): Label {
		return $this->createControl($label, $name, Label::class, $options);
	}
	
	/**
	 * 
	 * @param string $text
	 * @param array $options
	 * @return \Webos\Visual\Controls\Label
	 */
	public function createLabel(string $text, array $options = []): Label {
		return $this->createObject(Label::class, array_merge($options, ['text' => $text]));
	}
	
	/**
	 * 
	 * @param string $label
	 * @param string $name
	 * @param array $options
	 * @return \Webos\Visual\Controls\ComboBox
	 */
	public function createComboBox(string $label, string $name, array $options = []): ComboBox {
		return $this->createControl($label, $name, ComboBox::class, $options);
	}
	
	/**
	 * 
	 * @param string $label
	 * @param array $options
	 * @return \Webos\Visual\Controls\Button
	 */
	public function createButton(string $label, array $options = []): Button {
		return $this->createObject(Button::class, array_merge($options, ['value'=>$label]));
	}
	
	/**
	 * 
	 * @param string $text
	 * @param array $options
	 * @return \Webos\Visual\Controls\Button
	 */
	public function createLinkButton(string $text, string $url, array $options = []): LinkButton {
		return $this->createObject(LinkButton::class, array_merge($options, ['text'=> $text, 'url' => $url]));
	}
	
	public function createLink(string $text, string $url, array $options = []): Link {
		return $this->createObject(Link::class, array_merge($options, ['text'=> $text, 'url' => $url]));
	}
	
	/**
	 * @return \Webos\Visual\Controls\ToolBar
	 */
	public function createToolBar(array $params = []): ToolBar {
		$this->maxTopControl += 15;
		$toolBar = $this->createObject(ToolBar::class, array_merge([
			'top'  => $this->_getNextPosTop(),
			'left' =>  0,
			'right' => 0,
		], $params));
		$this->fixWindowHeight();
		return $toolBar;
	}
	
	public function createButtonsBar(): ToolBar {
		return $this->createToolBar([
			'fixedTo' => 'bottom',
			'horizontalAlign' => 'right',
		]);
	}
	
	public function createGetButtonsBar(): ToolBar {
		if (!$this->hasWindowButtons) {
			$this->hasWindowButtons = true;
			$this->buttonsBar = $this->createButtonsBar();
		}
		return $this->buttonsBar;
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
	public function createDataTable(array $options = []): DataTable {
		$dataTable = $this->createObject(DataTable::class, array_merge([
			'top'    => $this->_getNextPosTop(),
			'right'  => 0,
			'bottom' => 0,
		], $options));
		$this->maxTopControl += 400;
		$this->fixWindowHeight();
		return $dataTable;
	}
	
	/**
	 * @todo I don't remember why re-height container window...
	 * @param array $options
	 * @return Controls\Tree
	 */
	public function createTree(array $options = []): Tree {
		return $this->createObject(Tree::class, $options);
	}
	
	/**
	 * 
	 * @param array $options
	 * @return Controls\Frame
	 */
	public function createFrame(array $options = []): Frame {
		return $this->createObject(Frame::class, array_merge([
			'top'  => 0,
			'left' => 0,
			'right' => 0,
			'bottom' => 0,
		], $options));
	}
	
	public function createTabsFolder(array $params = []): MultiTab {
		$np = $this->_getNextPositions();
		
		return $this->createObject(MultiTab::class, array_merge(['top'=>$np['top']], $params));
	}
	
	public function createImage(string $filePath, array $params = []): Image {
		return $this->createObject(Image::class, $params)->setFilePath($filePath);
	}

	/**
	 * 
	 * @param type $caption
	 * @param type $width
	 * @param array $params
	 * @return \Webos\Visual\Controls\Button
	 */
	public function addHorizontalButton($caption, $width = 80, array $params = []): Button {
		if (!$this->horizontalButtonsBar) {
			$this->horizontalButtonsBar = $this->createToolBar();
		}
		$width = empty($params['width']) ? $width : $params['width'];
		$button = $this->horizontalButtonsBar->createObject(Button::class, array_merge($params, [
			'width' => $width,
			'value' => $caption,
		]));
		return $button;
	}
	
	public function createWindowLink(string $label, string $url, array $options = []): Link {
		return $this->createGetButtonsBar()->createLink($label, $url, $options);
	}
	
	public function createWindowButton($label, array $options = []): Button {
		return $this->createGetButtonsBar()->addButton($label, $options);
	}

	public function setFormData(array $data, bool $triggerUpdateValueEvent = false): void {
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
					if ($triggerUpdateValueEvent) {
						$childObject->triggerEvent('updateValue');
					}
				}
			}
		}
	}
	
	public function clearFormData(array $data, bool $triggerUpdateValueEvent = false): void {
		$objects = $this->getChildObjects();
		foreach($objects as $object) {
			if ($object instanceOf Field) {
				$object->value = null;
			}
		}
		$this->setFormData($data, $triggerUpdateValueEvent);
	}
	
	/**
	 * @return array
	 */
	public function getFormData(array $merge = []): array {
		$formData = [];
		$objects = $this->getChildObjects();
		foreach($objects as $childObject){
			if ($childObject->name) {
				$formData[$childObject->name] = $childObject->value;
			}
		}
		return array_merge($formData, $merge);
	}
	
	public function enableForm(): void {
		$objects = $this->getChildObjects();
		foreach($objects as $object) {
			if ($object instanceOf Field) {
				$object->disabled = false;
			}
		};
	}
	
	public function disableForm(): void {
		$objects = $this->getChildObjects();
		foreach($objects as $object) {
			if ($object instanceOf Field) {
				$object->disabled = true;
			}
		};
	}
	
	public function setMaxTopControl() {
		$data = $this->_getNextPositions();
		if ($data['top'] + $data['height'] > $this->maxTopControl) {
			$this->maxTopControl = $data['top'] + $data['height'];
		}
	}
	
	public function fixWindowHeight(): void {
		$this->getParentWindow()->height = $this->maxTopControl + 45;
	}
	
	public function splitVertical(int $distribution = 200, bool $draggable = true): VisualObject {
		$container = $this;
		$tickness = 10;
		if ($this instanceof Window) {
			$container = $this->createFrame([
				'left'   => 5,
				'right'  => 5,
				'bottom' => 5,
			]);
			$this->splittedFrames = $container;
		}
		$this->leftPanel = $container->createObject(Frame::class, [
			'top' => 0, 'bottom' => 0
		]);
		$this->verticalSeparator = $container->createObject(VerticalSeparator::class, [
			'width'=> $tickness, 'top' => 0, 'bottom' => 0, 'draggable' => $draggable,
		]);
		$this->rightPanel = $container->createObject(Frame::class, [
			'top' => 0, 'bottom' => 0
		]);
		if ($distribution < 0) {
			$this->verticalSeparator->right = abs($distribution);
		}
		if ($distribution > 0) {
			$this->verticalSeparator->left = abs($distribution);
		}
		return $this;
	}
	
	/**
	 * Split window in two horizontal panels
	 * 
	 * @param int $distribution
	 * @param bool $draggable
	 * @return VisualObject
	 */
	public function splitHorizontal(int $distribution = 200, bool $draggable = true): VisualObject {
		$container = $this;
		$tickness = 10;
		if ($this instanceof Window) {
			$container = $this->createFrame();
			$this->splittedFrames = $container;
		}
		$this->topPanel = $container->createObject(Frame::class, [
			'top' => 0, 'left' => 0, 'right' => 0,
		]);
		$this->horizontalSeparator = $container->createObject(HorizontalSeparator::class, [
			'height' => $tickness, 'left' => 0, 'right' => 0, 'draggable' => $draggable,
		]);
		$this->bottomPanel = $container->createObject(Frame::class, [
			'left' => 0, 'right' => 0, 'bottom' => 0,
		]);
		if ($distribution < 0) {
			$this->horizontalSeparator->bottom = abs($distribution);
		}
		if ($distribution > 0) {
			$this->horizontalSeparator->top = abs($distribution);
		}
		return $this;
	}
	
	public function createHTMLContainer(array $parameters = []): HtmlContainer {		
		return $this->createObject(HtmlContainer::class, $parameters);
	}
	
	public function createFilePicker(string $label = '', array $params = []): FilePicker {
		return $this->createControl($label, '', FilePicker::class, $params);
	}
	
	/**
	 * 
	 * @param type $top
	 * @param type $left
	 * @return ListItems
	 */
	public function createContextMenu($top, $left): ListItems {

		return $this->createObject(ListItems::class, [
			'top'      => $top,
			'left'     => $left,
			'position' => 'fixed',
		]);
	}
}