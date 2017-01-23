<?php
namespace Webos;

class FileHandler {
	public function openFile($fileName, $option = 'readwrite') {
		$options = array(
			'create'    => 'w+',
			'readwrite' => 'r+',
			'append'    => 'r+',
			'read'      => 'r',
			'write'     => 'w',
		);

		$op = &$options[$option];
		if (!isset($op)) {
			$op = $options['readwrite'];
		}

		if ($option == 'readwrite') {
			if (!file_exists($fileName)) $op = $options['create'];
		}

		$h = fopen($fileName, $op);

		return new File($fileName, $h);
	}

	public function closeFile(File $file) {
		fclose($file->getFH());
	}
}
