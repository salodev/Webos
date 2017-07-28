<?php
namespace Webos\Visual\Windows;
use \Webos\Visual\Window;

class Exception extends Window {

	public function initialize(array $params = []) {
		$this->title  = 'Exception trown';
		$this->width  = 800;
		$this->height = 500;
		
		// $e = $this->exception = $params['e'];
		$this->title = $params['class'] . ' thrown:';
		$this->createLabel($params['message'],[
			'top'   => 0,
			'left'  => 0,
			'right' => 0,
		]);
		$this->callStack = $this->createDataTable([
			'top'    => 50,
			'left'   => 0,
			'right'  => 0,
			'bottom' => 94,
		]);
		// $this->trace = $e->getTrace();
		$this->callStack->addColumn('id', '#', 30, false, false, 'right');
		$this->callStack->addColumn('function', 'Function', 500);
		$this->callStack->addColumn('location', 'Location', 300);
		
		$this->callStack->rows = $params['rsTrace'];
		$this->callStack->bind('rowClick', [$this, 'onCallStackRowclick']);
		$this->widthFieldControl = 400;
		$this->widthLabelControl = 90;
		$this->topControl = 368;
		$this->createTextBox('File', 'file');
		$this->createTextBox('Line', 'line');
		$this->createTextBox('Function', 'function');
		$this->disableForm();
	}
	
	/**
	 * To avoid cause Closure serialize error.
	 * 
	 * @param \Exception $e
	 * @return array
	 */
	static public function ParseException(\Exception $e): array {
		$exceptionClass = get_class($e);
		$message = "'{$e->getMessage()}' in file {$e->getFile()} ({$e->getLine()})";
		$rsTrace = [];
		foreach($e->getTrace() as $k => $info) {
			$class = &$info['class'];
			$type = &$info['type'];
			$argumentsString = '';
			$argsList = [];
			if (!empty($info['args'])) {
				foreach($info['args'] as $arg) {
					if (is_string($arg)) {
						$argsList[] = "'{$arg}'";
					} elseif (is_numeric($arg)) {
						$argsList[] = "{$arg}";
					} elseif(is_callable($arg)) {
						$argsList[] = "closure()";
					} elseif (is_object($arg)) {
						$argsList[] = get_class($arg)."(...)";
					} elseif (is_array($arg)) {
						$argsList[] = 'array('.count($arg).')';
					} elseif (is_null($arg)) {
						$argsList[] = 'NULL';
					} else {
						$argsList[] = '??'. gettype($arg).'??';
					}
				}
				$argumentsString = implode(', ', $argsList);
			}
			$file = &$info['file'];
			$line = &$info['line'];
			$rsTrace[] = [
				'id' => "$k ",
				'function' => $class . $type . $info['function'] . '(' . $argumentsString . ')',
				'location' => '../' . basename($file) . ' (' . $line . ')',
				'file' => &$info['file'],
				'line' => &$info['line'],
				'args' => '', //&$info['args'],
			];
		}
		
		return [
			'class'   => $exceptionClass,
			'message' => $message,
			'rsTrace' => $rsTrace,
		];
	}

	public function onCallStackRowclick() {
		
		$row = $this->callStack->getSelectedRowData();
		if ($row) {
			$this->setFormData($row);
		}
	}
}