<?php

namespace Webos\Visual\Windows;

use Webos\Visual\Window;
use Webos\Visual\Controls\Frame;

class Wizard extends Window {
	
	protected $frames = [];
	protected $currentFrameIndex = 0;
	
	public function preInitialize(): void {
		
		parent::preInitialize();
		
		$this->btnPrevious = $this->createWindowButton('Previous');
		$this->btnNext     = $this->createWindowButton('Next'    );
		$this->btnFinish   = $this->createWindowButton('Finish'  );
		
		$this->btnPrevious->onClick(function() {
			$this->previous();
		});
		
		$this->btnNext->onClick(function() {
			$this->next();
		});
		
		$this->btnFinish->onClick(function() {
			$this->close();
			$this->triggerEvent('wizardFinished');
		});
		
		$this->enableEvent('wizardFinished');
	}
	
	public function addStep(): Frame {
		
		$frame = $this->createFrame([
			'top'   => 0,
			'left'  => 0,
			'right' => 0,
			'bottom' => 30,
		]);
		
		$this->frames[] = $frame;
		
		$this->goToFirst();
		
		return $frame;
	}
	
	public function goToFirst() {
		$this->currentFrameIndex = 0;
		$this->showFrame();
	}
	
	public function goToLast() {
		$this->currentFrameIndex = count($this->frames)-1;;
		$this->showFrame();
	}
	
	public function next() {
		$this->currentFrameIndex++;
		$maxIndex = count($this->frames)-1;
		if ($this->currentFrameIndex > $maxIndex) {
			$this->currentFrameIndex = $maxIndex;
		}
		$this->showFrame();
	}
	
	public function previous() {
		$this->currentFrameIndex--;
		if ($this->currentFrameIndex < 0) {
			$this->currentFrameIndex = 0;
		}
		$this->showFrame();
	}
	
	public function showFrame() {
		foreach($this->frames as $frame) {
			$frame->hide();
		}
		$frame = $this->frames[$this->currentFrameIndex];
		$frame->show();
		$this->setupButtons();
		$this->modified();
	}
	
	public function setupButtons() {
		$this->btnPrevious->disabled = true;
		$this->btnNext    ->disabled = true;
		$this->btnFinish  ->disabled = true;
		
		$maxIndex = count($this->frames)-1;
		
		if ($this->currentFrameIndex>0) {
			$this->btnPrevious->disabled = false;
		}
		
		if ($this->currentFrameIndex < $maxIndex) {
			$this->btnNext->disabled = false;
		}

		if ($this->currentFrameIndex == $maxIndex) {
			$this->btnFinish->disabled = false;
		}
	}
	
}