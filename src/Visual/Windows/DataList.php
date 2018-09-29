<?php

namespace Webos\Visual\Windows;
use Webos\Visual\Window;
use Webos\Visual\Controls\DataTable\Column;
use Webos\Visual\Controls\Button;

class DataList extends Window {
	
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
	
	public function initialize(array $params = []) {
		$this->width     = 600;
		$this->toolBar   = $this->createToolBar();
		$this->txtSearch = $this->toolBar->createTextBox(['placeholder'=>'...']);
		$this->btnSearch = $this->toolBar->createButton('Search');
		$this->dataTable = $this->createDataTable();
		
		$this->refreshList();
		
		$this->txtSearch->onLeaveTyping(function () {
			$this->refreshList();
		});
		
		$this->btnSearch->onClick(function() {
			$this->refreshList();
		});
		
		$this->onNewData(function() {
			$this->refreshList();
		});
		
		$this->txtSearch->focus();
	}
	
	public function refreshList() {
		$rs = $this->getData();
		$this->dataTable->rows = $rs;
	}
	
	public function getData(): array {
		return [];
	}
	
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
	
	public function addColumn(): Column {
		return $this->dataTable->addColumn();
	}
	
	public function addToolButton(string $title): Button {
		return $this->toolBar->addButton($title);
	}
}