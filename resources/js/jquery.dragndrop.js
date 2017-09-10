jQuery.fn.draggable = function() {
	$(this).each(function(){
		
		var $this = $(this);
		
		var onMouseDown = function(ev) {
			ev.stopPropagation();
			if (ev.button !==0 ){return;}
			document.dragStatus = true;
			document.el = $(this);
			document.dragOffsetX = ev.offsetX;
			document.dragOffsetY = ev.offsetY;
		};
		
		var onMouseUp = function(ev) {
			document.dragStatus = false;
			ev.stopPropagation();
			$(document.el).trigger('drop');
		}
		$this.mousedown(onMouseDown);
		// console.log('adding mouseup', this);
		$this.mouseup(onMouseUp);
		
		this.undrag = function() {
			$this.unbind('mousedown', onMouseDown);
			// console.log('removing mouseup', this);
			$this.unbind('mouseup', onMouseUp);
		}
	});
};

jQuery.fn.undrag = function() {
	$(this).each(function() {
		this.undrag();
	});
};

jQuery.fn.ondrag = function(fn) {
	$(this).each(function(){
		$(this).bind('drag', fn);
	});
};

jQuery.fn.ondrop = function(fn) {
	$(this).each(function(){
		$(this).bind('drop', fn);
	});
};

$(function() {
	$(document).mousemove(function(ev) {
		if (!document.dragStatus) {
			return;
		}
		var $el = $(document.el);
		var offset = $el.parent().offset();
		var offsetX = document.dragOffsetX + offset.left;
		var offsetY = document.dragOffsetY + offset.top;
		$el.css('left', ev.clientX-offsetX);
		$el.css('top',  ev.clientY-offsetY);
		$el.trigger('drag');
	});
});