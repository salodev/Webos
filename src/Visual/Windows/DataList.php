<?php

namespace Webos\Visual\Windows;
use Webos\Visual\Window;
use Webos\Visual\Controls\DataTable\Column;
use Webos\Visual\Controls\Button;
use Webos\Visual\Controls\Menu\Item;

abstract class DataList extends Window {
	
	/**
	 *
	 * @var \Webos\Visual\Controls\ToolBar
	 */
	public $toolBar   = null;
	
	/**
	 *
	 * @var \Webos\Visual\Controls\TextBox 
	 */
	public $txtSearch = null;
	
	/**
	 *
	 * @var \Webos\Visual\Controls\Button
	 */
	public $btnSearch = null;
	
	/**
	 *
	 * @var \Webos\Visual\Controls\DataTable
	 */
	public $dataTable = null;
	
	public function preInitialize() {
		parent::preInitialize();
		$this->width     = 600;
		$this->toolBar   = $this->createToolBar();
		$this->txtSearch = $this->toolBar->createTextBox(['placeholder'=>'...']);
		$this->btnSearch = $this->toolBar->createButton('Search');
		$this->dataTable = $this->createDataTable();
		
		$this->txtSearch->onLeaveTyping(function () {
			$this->refreshList();
		});
		
		$this->onKeyEscape(function() {
			if ($this->txtSearch->value!==null) {
				$this->txtSearch->value = null;
				$this->refreshList();
			} else {
				$this->close();
			}
		});
		
		$this->btnSearch->onClick(function() {
			$this->refreshList();
		});
		
		$this->onNewData(function() {
			$this->refreshList();
		});
		
		$this->dataTable->setDataFn(function(int $offset = 0, int $limit = 0) {
			return $this->getData($offset, $limit);
		});
		
		$this->dataTable->bind('nextPage', function($params) {
			$this->getData($params);
		});
		
		$this->txtSearch->focus();
	}
	
	public function afterInitialize() {
		parent::afterInitialize();
		$this->refreshList();
	}
	
	public function refreshList() {
		$rs = $this->dataTable->setOffset(0);
	}
	
	abstract public function getData(int $offset = 0, int $limit = 0): array;
	
	/**
	 * Usage example:
	 * 
	 * $this->createActionsMenu(function($data) {
	 *		$menu = $data['menu'];
	 *		$menu->creteItem('Edit')->openWindow('...');
	 * });
	 * @param \Webos\Visual\Windows\callable $fn
	 * @param string $title
	 * @return \self
	 */
	public function createActionsMenu(callable $fn, string $title = 'Actions'): self {
		$actionsMenu = $this->toolBar->actionsMenu = $this->toolBar->createDropDown($title);
		$actionsMenu->onContextMenu($fn);
		$this->dataTable->onContextMenu($fn);
		return $this;
	}
	
	public function addColumn(string $fieldName = '', string $label = '', int $width=100, bool $allowOrder=false, bool $linkable=false, string $align = 'left'): Column {
		return $this->dataTable->addColumn($fieldName, $label, $width, $allowOrder, $linkable, $align);
	}
	
	public function addToolButton(string $title): Button {
		return $this->toolBar->addButton($title);
	}
}