<?php

namespace Webos\Visual\Windows;

use Webos\Visual\Window;
use Webos\Visual\Controls\DataTable\Column;
use Webos\Visual\Controls\Button;

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
	
	/**
	 *
	 * @var bool 
	 */
	public $showSearch = true;
	
	/**
	 *
	 * @var string 
	 */
	public $searchPlaceHolderText = 'Search...';
	
	/**
	 *
	 * @var string 
	 */
	public $searchButtonText = 'Search';
	
	/**
	 * 
	 * @var int;
	 */
	public $queryLimit = 100;
	
	public function preInitialize():void {
		
		/**
		 * If parent make preinitalize actions, we need be run.
		 */
		parent::preInitialize();
		
		/**
		 * Setup default inital width
		 */
		$this->width = 600;
		
		/**
		 * Creates default toolbar
		 */
		$this->toolBar = $this->createToolBar();
		
		/**
		 * Setup ui for search toolbar
		 */
		if ($this->showSearch) {
			
			/**
			 * Add search textbox
			 */
			$this->txtSearch = $this->toolBar->createTextBox([
				'placeholder' => $this->searchPlaceHolderText,
			]);
			
			/**
			 * Setup as default capture keyboard typing control
			 */
			$this->txtSearch->captureTyping(true);
			
			/**
			 * A mouse control button for search.
			 */
			$this->btnSearch = $this->toolBar->createButton($this->searchButtonText);
			
			/**
			 * When the user stop typing we make the filter
			 */
			$this->txtSearch->onLeaveTyping(function() {
				$this->refreshList();
			});
		
			/**
			 * If click the buton make same action.
			 */
			$this->btnSearch->onClick(function() {
				$this->refreshList();
			});
		
			/**
			 * Finally visual focus
			 */
			$this->txtSearch->focus();
		}
		
		/**
		 * Create the data table
		 */
		$this->dataTable = $this->createDataTable();
		
		/**
		 * Because all Windows implement closing by Escape key press,
		 * we 'intercept' close event and can prevent action if necessary
		 * by returning false.
		 */
		$this->onClose(function() {
			/**
			 * Just for search impelmentation
			 */
			if ($this->showSearch) {
				
				/**
				 * If text search is fulfilled, we clear it and prevent close
				 * If not, close will performed.
				 */
				if ($this->txtSearch->value!==null) {
					$this->txtSearch->value = null;
					$this->refreshList();
					return false;
				}
			}
		});
		
		/**
		 * New data is the common event through windows for add or editions
		 * notifications
		 */
		$this->onNewData(function() {
			
			/**
			 * In this case refresh list to show to user updated information.
			 */
			$this->refreshList();
		});
		
		/**
		 * Because dataTable implements a data consumer, we use a single way.
		 */
		$this->dataTable->setDataFn(function(int $offset = 0, int $limit = 0) {
			return $this->getData($offset, $limit);
		});
	}
	
	/**
	 * On initialize user can customize the window,
	 * since list is refresh.
	 */
	public function afterInitialize() {
		parent::afterInitialize();
		$this->refreshList();
	}
	
	/**
	 * Refresh list with updated data.
	 */
	public function refreshList() {
		
		/**
		 * For 'refrsh' action ever starts from begin.
		 */
		$rs = $this->dataTable->setOffset(0, $this->queryLimit);
	}
	
	/**
	 * User MUST implement get data method.
	 */
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
	
	/**
	 * A shorthand method to add columns to datatable.
	 */
	public function addColumn(string $fieldName = '', string $label = '', int $width=100, bool $allowOrder=false, bool $linkable=false, string $align = 'left'): Column {
		return $this->dataTable->addColumn($fieldName, $label, $width, $allowOrder, $linkable, $align);
	}
	
	/**
	 * A shorthand method to add a button on toolbar.
	 */
	public function addToolButton(string $title): Button {
		return $this->toolBar->addButton($title);
	}
}