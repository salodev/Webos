<?php

namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Exception;
use Webos\StringChar;
use salodev\FileSystem\File;

class Image extends Control {
	
	public function initialize(array $params = []): void {
		
	}
	
	/**
	 * Sets the image source file from local (server) file path.
	 * This path will not see on the web render.
	 * @param string $filePath
	 */
	public function setFileSource(string $filePath): self {
		$this->filePath = $filePath;
	}
	
	public function serveImageFile() {
		$file     = $this->getFile();
		$mimeType = $file->getMimeType();
		$content  = $file->getAllContent();
		
		header('Content-Type: ' . $mimeType);
		echo $content;
		die(); // protect undesired outputs
	}
	
	public function getFile(): File {
		return File::GetInstance($this->filePath);
	}
	
	
	public function render(): string {
		if ($this->embedContent || true) {
			$file = $this->getFile();
			$mimeType = $file->getMimeType();
			$encodedContent = base64_encode($file->getAllContent());
			$src  = "data:{$mimeType};base64, {$encodedContent}";
		} else {
			$src  = '/?actionName=serveImageFile&objectID=' . $this->getObjectID();
		}
		$html = "<img id=\"{$this->getObjectID()}\" {$this->getInlineStyle()} src=\"{$src}\" />";
		return $html;
	}
	
	public function getAllowedActions(): array {
		$arr = parent::getAllowedActions();
		$arr[] = 'serveImageFile';
		return $arr;
	}
	
	public function openUploadWindow() {
		$uw = $this->getApplication()->openWindow();
		$uw->file = $uw->createFileBox();
		$uw->addHorizontalButton('Cancel')->closeWindow();
		$uw->addHorizontalButton('Upload')->onClick(function() {
			$uw->file->checkUploadedFile();
			
		});
	}
	
}