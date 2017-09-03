__registerEventListener('updateElements', function(data){
	var i;
	for(i in data) {
		var objectId = data[i].objectId;
		var content  = data[i].content;
		var $object  = $('#'+objectId);
		$object.replaceWith(content);
		$object  = $('#'+objectId); // re-select becuase original was lost by "replaceWith" call;
		
		(function(objectId) {
			var $object = $('#'+objectId);
			setTimeout(function() {
				Directives.applyAll($object);
				Directives.findNApplyAll($object);
			}, 100); // no entiendo porqué... $object no está disponible en el DOM y tengo que eperar ...
		})(objectId);
	}
	__triggerEvent('elementsUpdated');
});

__registerEventListener('createElements', function(data) {
	var i;
	for (i in data) {
		(function(create) {
			var parentObjectId = create.parentObjectId;
			var content        = create.content;
			var $container     = $(document.body);

			if (parentObjectId) {
				var containerSelector = '#' + parentObjectId + ' > .container';
				if ($(containerSelector).length) {
					$container = $(containerSelector);
				} else {
					$container = $('#' + parentObjectId);
				}
			}
			$container.append(content);
			Directives.findNApplyAll($container);
		})(data[i]);
	}
	__triggerEvent('elementsUpdated');
});

__registerEventListener('removeElements', function(data) {
	var i;
	for (i in data) {
		$('#' + data[i].objectId).remove();
	}
	__triggerEvent('elementsUpdated');
});

__registerEventListener('authUser', function() {
	location.href='/landing.php?p=login';
});
__registerEventListener('changeEnterprise', function() {
	location.href='empresa.php';
});

/**
 * Este observador se encarga de acomodar los MenuList disponibles.
 **/
__registerEventListener('elementsUpdated', function() {
	$('.MenuList').each(function(){
		var $prev = $(this).prev();
		if (!$prev.length) return;

		var $selectedItem = $prev.find('.MenuItem.selected');
		if (!$selectedItem.length) return;

		var $this = $(this);
		var $menuItems = $prev.find('.MenuItem');

		var selPos = 0;
		for (var i=0; i<$menuItems.length; si++) {
			var $menuItem = $($menuItems[i]);
			if ($menuItem.hasClass('selected')) {
				selPos = i;
				break;
			}
		}

		var top = parseInt($prev.css('top')) + parseInt($menuItems.height()) * selPos + 'px';
		var left = parseInt($prev.css('left')) + $prev.outerWidth() - 1 + 'px';

		$this.css('top', top);
		$this.css('left', left);
	});
});

/**
 * Este observador se encarga de acomodar los MenuList disponibles.
 **/
__registerEventListener('elementsUpdated', function() {
	$('.form-wrapper.active').each(function(){
		this.parentNode.appendChild(this);
	});

	/*var activeWindow = $activeWindow[0];
	activeWindow.parentNode.appendChild(activeWindow);*/
});

$(document).ready(function() {
	__triggerEvent('elementsUpdated');
});

jQuery.fn.drag = function(callback) {
	var dragging = false;
	$(this).each(function() {

		$(this).mousedown(function() {
			dragging = true;
		});

		$(this).mousemove(function(event) {
			if (dragging) {
				callback.call($(this), event);
			}
		});

		$(this).mouseup(function() {
			dragging = false;
		});

		$(this).mouseout(function() {
			dragging = false;
		});
	});
}