<?php
namespace Webos;

class File {

	private $fh = null;
	public function __construct($fileName) {
		$this->fileName = $fileName;
	}
	
	public function open(string $option = 'readwrite'): self {
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
			if (!file_exists($this->fileName)) { 
				$op = $options['create'];
			}
		}

		$this->fh = fopen($this->fileName, $op);
		return $this;
	}
	
	public function close(): void {
		fclose($this->fh);
	}

	public function getFileName(): string {
		return $this->fileName;
	}

	public function getContent() {
		$content = fread($this->fh, filesize($this->fileName)+10);
		return $content;
	}

	public function putContent($content): self {
		fwrite($this->fh, $content);
		return $this;
	}
}