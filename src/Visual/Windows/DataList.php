<?php

namespace Webos\Visual\Windows;
use Webos\Visual\Window;

class DataList extends Window {
	public $toolBar   = null;
	
	/**
	 *
	 * @var Webos\Visual\Controls\TextBox 
	 */
	public $txtSearch = null;
	public $btnSearch = null;
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
		
		$this->txtSearch->focus();
	}
	
	public function refreshList() {
		$rs = $this->getData();
		$this->dataTable->rows = $rs;
	}
	
	public function getData(): array {
		return [];
	}
	
	public function createActionsMenu(callable $fn) {
		$this->toolBar->createDropDown('Acciones')->onContextMenu($fn);
		$this->dataTable->onContextMenu($fn);
	}
}