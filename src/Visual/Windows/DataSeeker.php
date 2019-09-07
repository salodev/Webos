<?php

namespace Webos\Visual\Windows;

abstract class DataSeeker extends DataList {
	
	public function preInitialize(array $params = []):void {
		parent::preInitialize($params);
		
		$this->title = 'Data seeker window';
		$this->modal = true;
		
		$this->createActionsMenu(function($data) {
			$menu = $data['menu'];
			if ($this->dataTable->hasSelectedRow()) {
				$menu->createItem('Select')->onClick(function() {
					$this->close();
					$this->newData($this->dataTable->getSelectedRowData());
				});
				$menu->createSeparator();
			}
			$menu->createItem('Refresh...')->onClick(function() {
				$this->refreshList();
			});
		});
	}
}