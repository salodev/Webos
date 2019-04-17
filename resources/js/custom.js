Webos.bind('updateElements', function(data){
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
	Webos.trigger('elementsUpdated');
});

Webos.bind('createElements', function(data) {
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
	Webos.trigger('elementsUpdated');
});

Webos.bind('removeElements', function(data) {
	var i;
	for (i in data) {
		$('#' + data[i].objectId).remove();
	}
	Webos.trigger('elementsUpdated');
});

Webos.bind('authUser', function() {
	location.reload();
});
Webos.bind('sendFileContent', function() {
	location.search = '?getOutputStream=true';
});
Webos.bind('changeEnterprise', function() {
	location.href='empresa.php';
});
Webos.bind('loggedIn', function() {
	location.reload();
});

/**
 * Este observador se encarga de acomodar los MenuList disponibles.
 **/
Webos.bind('elementsUpdated', function() {
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
Webos.bind('elementsUpdated', function() {
	$('.form-wrapper.active').each(function(){
		this.parentNode.appendChild(this);
	});

	/*var activeWindow = $activeWindow[0];
	activeWindow.parentNode.appendChild(activeWindow);*/
});

$(document).ready(function() {
	Webos.trigger('elementsUpdated');
});