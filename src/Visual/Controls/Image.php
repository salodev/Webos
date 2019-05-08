<?php

namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Exception;
use Webos\StringChar;
use Webos\Stream\Content;
use Webos\Visual\Window;
use salodev\FileSystem\File;

class Image extends Control {
	
	public function initialize(array $params = []): void {
		$this->onClick(function() {
			if ($this->uploadEnabled) {
				$window = $this->openUploadWindow();
				$this->triggerEvent('uploadOpened', [
					'window' => $window,
				]);
			}
		});
	}
	
	public function enableUpload(bool $updateSource = true): self {
		$this->uploadEnabled = true;
		$this->updateSource = $updateSource;
		return $this;
	}
	
	public function disableUpload(): self {
		$this->uploadEnabled = false;
		return $this;
	}
	
	public function setFilePath(string $filePath): self {
		$this->setFile(new File($filePath));
		return $this;
	}
	
	public function setFile(File $file): self {
		$this->file = $file;
		$this->modified();
		return $this;
	}
	
	public function getFile(): File {
		return $this->file;
	}

	public function click() {
		$this->triggerEvent('click');
	}
	
	public function getMediaContent(array $parameters = []): Content {
		return Content::CreateFileContent($this->getFile()->getFullPath());
	}
	
	public function render(): string {
		if ($this->embedContent) {
			$file = $this->getFile();
			$mimeType = $file->getMimeType();
			$encodedContent = base64_encode($file->getAllContent());
			$src  = "data:{$mimeType};base64, {$encodedContent}";
		} else {
			$src  = '?getMediaContent=true&objectID=' . $this->getObjectID();
		}
		$directive = '';
		if ($this->hasListenerFor('click')) {
			$directive = ' webos click';
		}
		$html = "<img id=\"{$this->getObjectID()}\" {$this->getInlineStyle()} src=\"{$src}\" {$directive} />";
		return $html;
	}
	
	public function onUpload(callable $fn): self {
		$this->bind('upload', $fn);
		return $this;
	}
	
	public function onUploadOpen(callable $fn): self {
		$this->bind('uploadOpened', $fn);
		return $this;
	}
	
	public function openUploadWindow(): Window {
		if ($this->uploadWindow) {
			$this->uploadWindow->focus();
			return $this->uploadWindow;
		}
		$uw = $this->uploadWindow = $this->getParentWindow()->openWindow(Window::class);
		$uw->title = 'Select a file';
		
		$uw->file = $uw->createObject(FilePicker::class, [
			'top' => 10,
		])->onUpload(function($data, $source) {
			if ($this->updateSource) {
				$data['file']->moveTo($this->getFile()->getFullPath(), true);
				$this->setFile($data['file']);
			}
			
			$source->getParentWindow()->close();
			
			$this->triggerEvent('upload', [
				'file' => $data['file'],
			]);
		})->enabledTypes([
			'image/png',
			'image/jpg',
			'image/jpeg',
			'image/gif',
			'image/bmp',
			'image/x-icon',
			'image/svg+xml',
		]);
		
		$uw->createWindowButton('Cancel')->closeWindow();
		$uw->height = 150;
		
		$uw->onClose(function() {
			$this->uploadWindow = null;
		});
		
		return $uw;
	}
	
	public function getAllowedActions(): array {
		return array(
			'click',
		);
	}

	public function getAvailableEvents(): array {
		return array(
			'upload',
			'uploadOpened',
			'click',
		);
	}
	
}