<?php

namespace Webos\Visual\Windows;

abstract class DataSeeker extends DataList {
	
	public $actionMenuSelectText  = 'Select';
	public $actionMenuRefreshText = 'Refresh...';
	public $selectEventName       = 'select';
	public $selectARowMessageText = 'Please select a row';
	
	/**
	 * Default behavior for DataSeeker window, placed on preInitialize in
	 * order to keep free initialize() method.
	 */
	public function preInitialize(array $params = []): void {
		
		/**
		 * Because is DataList based, previously leave it do their stuffs.
		 */
		parent::preInitialize($params);
		
		$this->title = 'Data seeker window';
		$this->modal = true;
		
		/**
		 * Sets the context menu factory callback. It will be called
		 * once user right click on list.
		 */
		$this->createActionsMenu(function($data) {
			
			/**
			 * Menu is previously created and passed on 'menu' offset
			 */
			$menu = $data['menu'];
			
			if ($this->dataTable->hasSelectedRow()) {
				/**
				 * If user has selected row, so create menu item to select
				 * item and notify it.
				 */
				$menu->createItem($this->actionMenuSelectText)->onClick(function() {
					$this->select();
				});
				
				/**
				 * And creates a separator
				 */
				$menu->createSeparator();
			}
			
			/**
			 * Option for frefresh the list
			 */
			$menu->createItem($this->actionMenuRefreshText)->onClick(function() {
				$this->refreshList();
			});
		});
		
		/**
		 * By 'Enter' keypress user can select item.
		 */
		$this->dataTable->onKeyEnter(function() {
			$this->select();
		});
	}
	
	public function select(array $data = []): bool {
		
		/**
		 * As expected in majority cases no data is prvided
		 * because it must be taken from current list.
		 * 
		 */
		if (empty($data)) {
			
			/**
			 * If no row selected, no data to use.
			 */
			if (!$this->dataTable->hasSelectedRow()) {
				throw new \Exception($this->selectARowMessageText);
			}
			
			/**
			 * So, use selected row data
			 */
			$data = $this->dataTable->getSelectedRowData();
		}
		
		/**
		 * To prevent hang open on any error, first close it.
		 */
		$this->close(true);
		
		/**
		 * Otherwise, user may provided information.
		 * This is usefull for in-site entity creation features.
		 */
		return $this->triggerEvent($this->selectEventName, $data);
	}
	
	/**
	 * Shorthand to bind to select event.
	 */
	public function onSelected(callable $cb, bool $persistent = true, array $contextData = []): self {
		$this->bind($this->selectEventName, $cb, $persistent, $contextData);
		return $this;
	}
}