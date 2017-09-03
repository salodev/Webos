Directives.register('action', function(el) {
	var id = $(el).attr('id') || $(el).parents('[id]').attr('id');
	var data = $(el).data();
	$(el).unbind('click').bind('click', function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		Webos.action($(el).attr('action'), id, data);
	});
});

Directives.register('click', function(el) {
	var id = $(el).attr('id') || $(el).parents('[id]').attr('id');
	var data = $(el).data();
	$(el).unbind('click').bind('click', function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		Webos.action($(el).attr('click') || 'click', id, data);
	});
});

Directives.register('double-click', function(el) {
	var id = $(el).attr('id') || $(el).parents('[id]').attr('id');
	var data = $(el).data();
	$(el).unbind('dblclick').bind('dblclick', function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		Webos.action($(el).attr('double-click') || 'dblclick', id, data);
	});
});

Directives.register('close', function(el) {
	var id = $(el).parents('[id]').attr('id');
	$(el).unbind('click').bind('click', function(ev) {
		ev.preventDefault();
		Webos.action('close', id);
	});
});

Directives.register('leaveTyping', function(el) {
	var id = $(el).attr('id');
	var to = null;
	$(el).unbind('keyup').bind('keyup', function(ev) {
		if (to) { clearTimeout(to); }
		to = setTimeout(function() {
			Webos.action('leaveTyping', id, {
				value: $(el).val(),
				ignoreUpdateObject: true
			});	
		}, 400);
	});
});

Directives.register('ready', function(el) {
	var id = $(el).attr('id') || $(el).parents('[id]').attr('id');
	Webos.action('ready', id);
});

Directives.register('update-value', function(el) {
	$(el).unbind('change').bind('change', function() {
		Webos.action('setValue', $(el).attr('id'), {value:$(el).val()});
	});
});

Directives.register('set-scroll-values', function(el) {
	var $el = $(el);
	var id = $el.attr('id');
	var values = $(el).attr('set-scroll-values').split(',');
	$el.attr('disable-scroll-event', 'yes');
	$el.scrollTop(values[0]);
	$el.scrollLeft(values[1]);
	setTimeout(function() {
		$el.attr('disable-scroll-event', 'no');
	}, 600);
	
	var to = null;
	$el.unbind('scroll').bind('scroll', function() {
		if ($el.attr('disable-scroll-event')=='yes') {
			return;
		}
		if (to) { clearTimeout(to); }
		to = setTimeout(function() {
			Webos.action('scroll', id, {left:el.scrollLeft, top:el.scrollTop});
		}, 500);
	});
});

Directives.register('contextmenu', function(el){
	var $el = $(el);
	var id = $el.attr('id') || $el.parents('[id]').attr('id');
	
	$el.css('cursor','context-menu');
	$el.unbind('contextmenu').bind('contextmenu', function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		var value = $el.attr('contextmenu');
		Webos.action('contextMenu', id, {
			top:  ev.clientY,
			left: ev.clientX,
			data: value.length ? value : undefined
		});
	})
});

Directives.register('resize', function(el) {
	var id = $(el).attr('id') || $(el).parents('[id]').attr('id');
	var $el = $(el);
		
	var notifyResize = function() {
		Webos.action('resize', id, {
			x1: $el.offset().left,
			x2: $el.offset().left + $el.width(),
			y1: $el.offset().top,
			y2: $el.offset().top + $el.height(),
			ignoreUpdateObject: true
		});
		$el.find('.resize-handler').remove();
	}
	
	$el.mouseover(function(){
		// $el.find('.resize-handler').remove();
		if ($(this).find('.resize-handler').length) return null;
		var handlers = [
			// 'resize-handler top',
			// 'resize-handler top-right',
			'resize-handler right',
			'resize-handler bottom-right',
			'resize-handler bottom',
			// 'resize-handler bottom-left',
			// 'resize-handler left',
			// 'resize-handler left-top',
		];

		for (var i in handlers) {
			var handler = '<div class="CLASS"></div>'.replace('CLASS', handlers[i]);
			$(this).append($(handler));
		}
		
		(function() {
			var $h = $el.find('.resize-handler.right');
			$h.easydrag();
			$h.ondrag(function(e) {
				var w = $h.offset().left + $h.width() - $el.offset().left;
				$el.width(w);
			});
			$h.ondrop(function() {
				notifyResize();
			});
		})();
		
		(function() {
			var $h = $el.find('.resize-handler.bottom');
			$h.easydrag();
			$h.ondrag(function(e) {
				var h = $h.offset().top + $h.height() - $el.offset().top;
				$el.height(h);
			});
			$h.ondrop(function() {
				notifyResize();
			});
		})();
		
		(function() {
			var $h = $el.find('.resize-handler.bottom-right');
			$h.easydrag();
			$h.ondrag(function(e) {
				var w = $h.offset().left + $h.width()  - $el.offset().left;
				var h = $h.offset().top  + $h.height() - $el.offset().top;
				$el.width(w);
				$el.height(h);
			});
			$h.ondrop(function() {
				notifyResize();
			});
		})();
	});
	
	$el.mouseout(function() {
		$el.find('resize-handler').remove();
	});
});

Directives.register('move', function(el) {
	
	var id = $(el).attr('id') || $(el).parents('[id]').attr('id');
	var $el = $(el);
	var $container = $el.parents($el.attr('move')) || $el;
	
	$el.mouseover(function() {
		$container.easydrag();
		$container.ondrop(function() {
			var x = $container.css('left').replace('px','');
			var y = $container.css('top' ).replace('px','');

			Webos.action('move', id, {x: x, y: y});
		});
	});
	
	$el.mouseout(function() {
		$container.dragOff();
	});
});

Directives.register('set-object-pos', function(el) {
	var id = $(el).attr('id') || $(el).parents('[id]').attr('id');
	var pos = $(el).offset();
	$(el).data('top', pos.top);
	$(el).data('left', pos.left);
});

$(function() {
	Directives.findNApplyAll(document.body);
});
