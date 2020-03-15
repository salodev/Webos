<?php
namespace Webos\Visual\Windows;

use Webos\Visual\Window;
use Webos\Visual\Controls\TextBox;

class Exception extends Window {

	public function initialize(array $params = []) {
		$this->title  = 'Exception trown';
		$this->width  = 800;
		$this->height = 500;
		$this->modal  = true;
		
		$this->splitHorizontal(60);
		$this->bottomPanel->splitHorizontal(-100);
		$this->panels = [
			$this->topPanel,
			$this->bottomPanel->topPanel,
			$this->bottomPanel->bottomPanel,
		];
		
		// $e = $this->exception = $params['e'];
		$this->title = $params['class'] . ' thrown:';
		$this->panels[0]->createObject(TextBox::class)
			->value($params['message'])
			->top(0)
			->left(0)
			->right(0)
			->bottom(0)
			->multiline(true)
			->disable()->width='100%';
		
		/**
		 * @var \Webos\Visual\Controls\DataTable
		 */
		$this->callStack = $this->panels[1]->createDataTable([
			'top'    => 0
		]);
		// $this->trace = $e->getTrace();
		$this->callStack->addColumn('id', '#', 30, false, false, 'right');
		$this->callStack->addColumn('function', 'Function', 500);
		$this->callStack->addColumn('location', 'Location', 300);
		
		$this->callStack->rows = $params['rsTrace'];
		$this->callStack->onRowClick(function() {
			if ($this->callStack->hasSelectedRow()) {
				$row = $this->callStack->getSelectedRowData();
				$this->panels[2]->setFormData($row, true);
			}
		});
		$this->panels[2]->createTextBox('File', 'file', ['width'=>600]);
		$this->panels[2]->createTextBox('Line', 'line');
		$this->panels[2]->createTextBox('Function', 'function');
		$this->panels[2]->disableForm();
		$this->height=500;
		
		$this->callStack->onContextMenu(function($data) {
			$menu = $data['menu'];
			$menu->createItem('Ampliar...')->onClick(function() {
				$desc = $this->callStack->getSelectedRowData('function');
				// malísimo. Sólo par salir del paso.
				$w = $this->messageWindow("<div style=\"overflow-x:scroll; position:absolute;left:0;right:0;top:0;bottom:30px;\"><pre>{$desc}</pre></div>");
				$w->title = 'Detalle';
				$w->height = 250;
			});
		});
	}
}