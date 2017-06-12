<?php
namespace Webos\Visual;

class ExceptionWindow extends Window {

	public function initialize(array $params = []) {
		$this->title  = 'Exception trown';
		$this->width  = '800px';
		$this->height = '500px';
		if (!$params['e'] || !($params['e'] instanceof \Exception)) {
			throw new \Exception('ExceptionWindow must be open with exception parameter');
		}
		
		$e = $this->exception = $params['e'];
		$this->title = get_class($e) . ' thrown:';
		$this->createLabel("'{$e->getMessage()}' in file {$e->getFile()} ({$e->getLine()})",[
			'top' => '0',
			'left' => '0',
			'right' => '0',
		]);
		$this->callStack = $this->createDataTable([
			'top' => '50px',
			'left' => '0',
			'right' => '0',
			'bottom' => '94px',
		]);
		$this->trace = $e->getTrace();
		$this->callStack->addColumn('id', '#', '30px', false, false, 'right');
		$this->callStack->addColumn('function', 'Function', '500px');
		$this->callStack->addColumn('location', 'Location', '300px');
		$rows = array();
		foreach($e->getTrace() as $k => $info) {
			$class = &$info['class'];
			$type = &$info['type'];
			$argumentsString = '';
			if (count($info['args'])==1 && is_string($info['args'][0])) {
				$argumentsString = "'{$info['args'][0]}'";
			}
			$rows[] = [
				'id' => "$k ",
				'function' => $class . $type . $info['function'] . '(' . $argumentsString . ')',
				'location' => '../' . basename($info['file']) . ' (' . $info['line']. ')',
				'file' => $info['file'],
				'line' => $info['line'],
				'args' => $info['args'],
			];
		}
		$this->callStack->rows = $rows;
		$this->callStack->bind('rowClick', [$this, 'onCallStackRowclick']);
		$this->widthFieldControl = 400;
		$this->widthLabelControl = 90;
		$this->topControl = 368;
		$this->createTextBox('File', 'file');
		$this->createTextBox('Line', 'line');
		$this->createTextBox('Function', 'function');
		$this->disableForm();
	}

	public function onCallStackRowclick() {
		$row = $this->callStack->getActiveRowData();
		if ($row) {
			$this->setFormData($row);
		}
	}
}