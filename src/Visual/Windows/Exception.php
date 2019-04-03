<?php
namespace Webos\Visual\Windows;

use Webos\Visual\Window;
use Webos\Visual\Controls\TextBox;
use Exception as ExceptionClass;
use Throwable;

class Exception extends Window {

	public function initialize(array $params = []) {
		$this->title  = 'Exception trown';
		$this->width  = 800;
		$this->height = 500;
		
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
	}
	
	/**
	 * To avoid cause Closure serialize error.
	 * 
	 * @param \Exception $e
	 * @return array
	 */
	static public function ParseException(Throwable $e): array {
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
}