jQuery.fn.draggable = function() {
	$(this).each(function(){
		var $this = $(this);
		var offsetX = 0;
		var offsetY = 0;
        var onmousemove = function(ev) {
			$(document.el).css('position', 'absolute');
			$(document.el).css('left',     ev.clientX-offsetX);
			$(document.el).css('top',      ev.clientY-offsetY);
			$(document.el).trigger('drag');
        };
		$this.mousedown(function(ev) {
			ev.stopPropagation();
			if (ev.button !==0 ){return;}
			document.el = $(this);
			offsetX = ev.offsetX;
			offsetY = ev.offsetY;
			$(document).mousemove(onmousemove);
		});
		$this.mouseup(function(ev) {
			ev.stopPropagation();
			$(document).unbind('mousemove', onmousemove);
			$(document.el).trigger('drop');
		});
	});
};
jQuery.fn.ondrag = function(fn) {
	$(this).each(function(){
		$(this).bind('drag', fn);
	});
}
jQuery.fn.ondrop = function(fn) {
	$(this).each(function(){
		$(this).bind('drop', fn);
	});
}