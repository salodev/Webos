Directives.register('action', function(el) {
	var id = $(el).attr('id') || $(el).parents('[id]').attr('id');
	var data = $(el).data();
	var bubble = ($(el).parents('[id]').attr('bubble') || 'true') == 'true';
	$(el).unbind('click').bind('click', function(ev) {
		if (!bubble) {
			if (ev.target !== el) { return; }
		}
		ev.stopPropagation();
		ev.preventDefault();
		Webos.action($(el).attr('action'), id, data);
	});
});

Directives.register('click', function(el) {
	var $el = $(el);
	var id = $el.attr('id') || $el.parents('[id]').attr('id');
	var data = $el.data();
	$el.unbind('click').bind('click', function(ev) {
		if ($el.attr('stop-propagation')!==undefined) {
			ev.stopPropagation();
		}
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

Directives.register('leavetyping', function(el) {
	var $el = $(el);
	var id  = $el.attr('id');
	var to  = null;
	var value = $el.val();
	var save = function() {
		Webos.action('leaveTyping', id, { value: $el.val() }, true);
	}
	
	var leaveTypingHandler = function(ev) {
		ev.stopPropagation();		
		if ($el.val() !== value) {
			if (to) { clearTimeout(to); }
				to = setTimeout(function() {
				save();
			}, 400);
		}
		value = $el.val();
	}

	$el.unbind('keyup', leaveTypingHandler);
	$el.bind('keyup', leaveTypingHandler);

	$(function(){
		if ($el.is(":-webkit-autofill")) {
			save();
		}
	});
});

Directives.register('ready', function(el) {
	var id = $(el).attr('id') || $(el).parents('[id]').attr('id');
	Webos.action('ready', id);
});

Directives.register('update-value', function(el) {
	$(el).unbind('change').bind('change', function() {
		Webos.action('setValue', $(el).attr('id'), { value:$(el).val() }, true);
	});
});

Directives.register('set-scroll-values', function(el) {
	var $el = $(el);
	var id = $(el).attr('id') || $(el).parents('[id]').attr('id');
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
			Webos.action('scroll', id, {
				left: el.scrollLeft, 
				top:  el.scrollTop
			}, true);
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
	});
});

Directives.register('filepicker', function(el) {
	var $el     = $(el);
	var $form   = $el.find('form');
	var $input  = $form.find('input');
	var $iframe = $el.find('iframe');
	
	$input.change(function() {
		$form.submit();
		$input.attr('disabled', 'disabled');
	});
	
	$iframe.on('load', function(ev) {
		$input.removeAttr('disabled');
		var rawResponse = $(ev.target.contentDocument.body).find('*')[0].innerHTML;
		var decodedResponse = $('<textarea />').html(rawResponse).text();
		Webos.parseResponse(JSON.parse(decodedResponse));
	});
});

Directives.register('resize', function(el) {
	var id = $(el).attr('id') || $(el).parents('[id]').attr('id');
	var $el = $(el);
		
	var notifyResize = function() {
		Webos.action('resize', id, {
			x1: $el.offset().left,
			x2: $el.offset().left + $el.width(),
			y1: $el.offset().top,
			y2: $el.offset().top + $el.height()
		}, true);
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
		// $container.undrag();
	});
});

Directives.register('drag-horizontal', function(el) {
	var $el = $(el);
	$el.easydrag();
	var top = $el.offset().top;
	$el.mousedown(function(e) {
		$el.offset({top:top});
	});
	$el.ondrag(function(e) {
		$el.offset({top:top});
	});
	$el.ondrop(function() {
		$el.offset({top:top});
	});
});

Directives.register('ondrag-horizontal', function(el) {
	var $el = $(el);
	var $prev = $el.prev();
	var $next = $el.next();
	var top = $el.offset().top;
	$el.ondrag(function(e) {
		$el.offset({top:top});
		var w = $el.offset().left - $prev.offset().left;
		$prev.width(w);
		var l = $el.offset().left + $el.width();
		$next.offset({left:l});
	});
});

Directives.register('drag-vertical', function(el) {
	var $el = $(el);
	$el.easydrag();
	var left = $el.offset().left;
	$el.mousedown(function(e) {
		$el.offset({left:left});
	});
	$el.ondrag(function(e) {
		$el.offset({left:left});
	});
	$el.ondrop(function() {
		$el.offset({left:left});
	});
});

Directives.register('ondrag-vertical', function(el) {
	var $el = $(el);
	var $prev = $el.prev();
	var $next = $el.next();
	var left = $el.offset().left;
	$el.ondrag(function(e) {
		$el.offset({left:left});
		var h = $el.offset().top - $prev.offset().top;
		$prev.height(h);
		var t = $el.offset().top + $el.height();
		$next.offset({top:t});
	});
});

Directives.register('set-object-pos', function(el) {
	var id = $(el).attr('id') || $(el).parents('[id]').attr('id');
	var pos = $(el).offset();
	$(el).data('top', pos.top);
	$(el).data('left', pos.left);
});

Directives.register('remove-class', function(el) {
	var $el = $(el);
	var className = $el.attr('remove-class');
	$el.bind('click', function() {
		$el.removeClass(className);
	});
});

Directives.register('toggle-class', function(el) {
	var $el          = $(el);
	var className    = $el.attr('toggle-class');
	var removeOthers = $el.attr('remove-others')!==undefined;
	$el.bind('click', function() {
		if (removeOthers) {
			$el.parent().find('> *').removeClass(className);
		}
		$el.toggleClass(className);
	});
});

Directives.register('ondrop', function(el) {
	$(el).ondrop(function() {
		var id = $(el).attr('id') || $(el).parents('[id]').attr('id');
		var data = $(el).position();
		var ignoreUpdateObject = $(el).attr('ignore-update-object')? true: false;
		Webos.action('drop', id, data, ignoreUpdateObject);
	});
	
});

Directives.register('key-press', function(el) {
	var $el = $(el); //$(el).attr('id')?$(el):$(el).parents('[id]');
	var id = Directives.getObjectId(el);
	var keyPressHandler = function(ev, data) {
		ev.stopPropagation();
		
		var allowedKeys = ($el.attr('key-press')||'').split(',');
		var ignoreUpdateObject = $el.attr('ignore-update-object')? true: false;
		if (allowedKeys.indexOf(data.key) >= 0) {
			Webos.action($el.attr('key-press-action') || 'keyPress', id, {
				key: data.key 
			}, ignoreUpdateObject);
		}

	}
	$el.unbind('key-press', keyPressHandler);
	$el.bind('key-press', keyPressHandler);
});

/**
 * Make custom directive receptor of event.
 */
KeyboardDispatcher.addDirective('key-press', 'key-press-data-table');

/**
 * This is an special directive for data table.
 * Because is a complex component, It need some fine user inetarction customization
 * in orde to give a better usability, in this case for keyboard interactivity
 */
Directives.register('key-press-data-table', function(el) {
	var $el = $(el).is('[id]')?$(el):$(el).parents('[id]');
	var timeoutId;
	var $hole = $el.find('.DataTableHole');
	var $body = $el.find('.DataTableBody');
	
	/**
	 * By name is possible unbind this handler
	 * keeping others alive for same event name.
	 */
	var keyPressOnDataTable = function(ev, data) {
		/**
		 * Is very important stop the event propagation.
		 * Default DOM propagation event to parent is undesired behavior
		 * here, so stop it.
		 */
		ev.stopPropagation();
		
		/**
		 * If no rows, nothing to do.
		 */
		if (!($body.find('.DataTableRow').length>0)) {
			return;
		}
		
		var index    = $body.find('.DataTableRow.selected').index()/1||0;
		var max      = $body.find('.DataTableRow').length-1;	
		var pageSize = Math.ceil($hole.height() / ($body.find('.DataTableRow:first-child()').height()||0));
		
		if (['ArrowUp','ArrowDown', 'PageUp', 'PageDown', 'Home', 'End'].indexOf(data.key)>=0) {
			$el.focus();
			if (data.key === 'Home') {
				index = 0;
			}
			if (data.key === 'End') {
				index = max;
			}
			if (data.key === 'PageUp') {
				index -= pageSize;
			}
			if (data.key === 'PageDown') {
				index += pageSize;
			}
			if (data.key === 'ArrowUp') {
				index--;
			}
			if (data.key === 'ArrowDown') {
				index++;
			}
			
			if (index < 0  ) { index = 0;   }
			if (index > max) { index = max; }
			
			$body.find('.DataTableRow.selected').removeClass('selected');
			$body.find('.DataTableRow:nth-child('+(index+1)+')').addClass('selected');
			
			/**
			 * Calculations about hole window and cursor position.
			 */
			var $selected = $body.find('.DataTableRow.selected');
			var wheight   = $hole.height();
			var wstart    = $hole.scrollTop();
			var wend      = $hole.scrollTop() + $hole.height();
			var rheight   = $selected.height();
			var rstart    = index * rheight;
			var rend      = rstart + rheight;
			
			/**
			 * If selected row is out of bounds, so scroll hole according
			 *  where is out.
			 */
			if (rend>wend) {
				$hole.scrollTop(rend - wheight);
			}
			if (rstart<wstart) {
				$hole.scrollTop(rstart);
			}

			/**
			 * Delayed ajax call, allow make it when user stop pressing keys or 
			 * leave hanged key. So timeout is freed and performed against webserver
			 */
			clearTimeout(timeoutId);
			timeoutId = setTimeout(function() {
				Webos.action('rowClick', $el.attr('id'), {
					row: index,
					fieldName:''
				}, true);
			}, 500);
		}
	};
	
	/**
	 * Because directive is reapplied once element is updated
	 * is need remove bound handler and set again, to avoid double execution call
	 */
	$el.unbind(keyPressOnDataTable);
	$el.bind('key-press', keyPressOnDataTable);
});

Directives.register('key-press-type', function(el) {
	$(el).bind('key-press-type', function(ev, data) {
		ev.stopPropagation();
		if (el !== $(':focus')[0]) { // not focused
			//$(el).val('');
			$(el).focus();
		}
	});
});

Directives.register('focus', function(el) {
	($(el).is('[id]')?$(el):$(el).parents('[id]')).focus();
});

$(function() {
	//wait for autofill
	setTimeout(function() {
		Directives.findNApplyAll(document.body);
	}, 400);
});