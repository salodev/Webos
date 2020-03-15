<?php

namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Exception;
use Webos\StringChar;
use Webos\Visual\Window;

class Image extends Control {
	
	public function initialize(array $params = []): void {
		$this->onClick(function() {
			if ($this->uploadEnabled) {
				$window = $this->openUploadWindow();
			}
		});
	}
	
	public function enableUpload(bool $updateSource = true): self {
		$this->uploadEnabled = true;
		$this->updateSource = $updateSource;
		$this->cursor = 'pointer';
		return $this;
	}
	
	public function disableUpload(): self {
		$this->uploadEnabled = false;
		$this->cursor = 'normal';
		return $this;
	}
	
	public function render(): string {
		$src = $this->getMediaContentForSrc($this->embedContent||false);
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
		
		$this->triggerEvent('uploadOpened', [
			'window' => $this->uploadWindow,
		]);
		
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
	
}