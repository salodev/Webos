<?php

namespace Webos\Visual\Windows;

class DataSeeker extends DataList {
	
	public function initialize(array $params = []) {
		$this->title = 'Data seeker window';
		parent::initialize($params);
		
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