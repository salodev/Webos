/**
 * Configuro la acción que enviará los datos al servidor
 * y procesará la respuesta convenientemente.
 *
 * También se encargará de hacer los cambios necesarios de acuerdo con
 * la lista de elementos a crear, actualizar o eliminar que informa el
 * servidor.
 **/
__registerAction('send', function(params) {
    if (typeof(params.actionName)=='undefined') return false;
    if (typeof(params.objectId)=='undefined') return false;

    $.ajax({
        url:     'ajax.php',
        data:    params,
		type:    'post',
        success: function(data){

			if (typeof(data.errors) != 'undefined' && data.errors.length) {
				var i, error;
				var txt = '';
				for (i in data.errors) {
					error = data.errors[i];
					txt += 'ERROR: ' + error.message + '\n';
					txt += 'at file [' + error.file + '] line [' + error.line + ']\n';
					txt += 'stack:\n';
					txt += error.trace;
					txt += '\n\n';
				}
				alert(txt);
			}

			if (typeof(data.events) != 'undefined') {
				var i, eventName, eventData;
				for (i in data.events) {
					eventName = data.events[i].name;
					eventData = data.events[i].data;

					__triggerEvent(eventName, eventData);
				}
			}
			return;

            if(typeof(data.update) != 'undefined') {
                for(i in data.update) {
                    var objectId = data.update[i].objectId;
                    var content  = data.update[i].content;

                    $('#'+objectId).replaceWith(content);
                }
                __triggerEvent('elementsUpdated');
            }

			if (typeof(data.create) != 'undefined') {
				for (i in data.create) {
					(function(create) {
						var parentObjectId = create.parentObjectId;
						var content        = create.content;

						if (parentObjectId) {
							var containerSelector = '#' + parentObjectId + ' .container';
							if ($(containerSelector).length) {
								$(containerSelector).append(content);
							} else {
								$('#' + parentObjectId).append(content);
							}
						} else {
							$(document.body).append(content);
						}
					})(data.create[i]);
				}
				__triggerEvent('elementsUpdated');
			}

			if (typeof(data.remove) != 'undefined') {
				for (i in data.remove) {
					$('#' + data.remove[i].objectId).remove();
				}
				__triggerEvent('elementsUpdated');
			}
        }
    });
});

__registerEventListener('updateElements', function(data){
	var i;
	for(i in data) {
		var objectId = data[i].objectId;
		var content  = data[i].content;

		$('#'+objectId).replaceWith(content);
	}
	__triggerEvent('elementsUpdated');
});

__registerEventListener('createElements', function(data) {
	var i;
	for (i in data) {
		(function(create) {
			var parentObjectId = create.parentObjectId;
			var content        = create.content;

			if (parentObjectId) {
				var containerSelector = '#' + parentObjectId + ' > .container';
				if ($(containerSelector).length) {
					$(containerSelector).append(content);
				} else {
					$('#' + parentObjectId).append(content);
				}
			} else {
				$(document.body).append(content);
			}
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
	location.href='login.php';
});
__registerEventListener('changeEnterprise', function() {
	location.href='empresa.php';
});

__registerEventListener('elementsUpdated', function() {
    /**
     * Cuando pongo el mouse sobre la barra de título,
     * el formulario tiene que ser 'draggable'.
     **/
    $('.form-wrapper').unbind();
    $('.form-wrapper .form-titlebar .title').mouseover(function(){
        $('.form-wrapper').easydrag();
    });

    /**
     * Cuando quito el mouse de la barra de título,
     * el formulario no es más 'draggable'.
     **/
    $('.form-wrapper .form-titlebar .title').mouseout(function() {
        $('.form-wrapper').unbind().undrag();
    });

    /**
     * Necesito concer la posición donde se dejó el formulario
     * para enviarla al servidor.
     **/
    $('.form-wrapper').ondrop(function(e) {
        $elem = $(e.target).parents('.form-wrapper');
        x = $elem.css('left').replace('px','');
        y = $elem.css('top').replace('px','');

        var formPositionInfo = {
            actionName:'move',
            objectId: $elem.attr('id'),
            x: x, y:y
        }
		__doAction('send',formPositionInfo);
    });
});

__registerEventListener('elementsUpdated', function() {
	//return null;
	$('.form-wrapper').mouseover(function(){
		if ($(this).find('.resize-handler').length) return null;
		var handlers = [
			'resize-handler top',
			'resize-handler top-right',
			'resize-handler right',
			'resize-handler bottom-right',
			'resize-handler bottom',
			'resize-handler bottom-left',
			'resize-handler left',
			'resize-handler left-top',
		]

		for (i in handlers) {
			var handler = '<div class="CLASS"></div>'.replace('CLASS', handlers[i]);
			$(this).append($(handler));
		}
	});

	$('.form-wrapper').mouseout(function(){
		//$(this).find('.resize-handler').remove();
	});

	$('.resize-handler.bottom-right').unbind().drag(function(e) {
		var $window = $(this).parent();
		$window.height(e.clientY - $window.offset().top);
		$window.width(e.clientX - $window.offset().left);
	});
});

/**
 * Este observador se encarga de acomodar los MenuList disponibles.
 **/
__registerEventListener('elementsUpdated', function() {
	$('.MenuList').each(function(){
		$prev = $(this).prev();
		if (!$prev.length) return;

		$selectedItem = $prev.find('.MenuItem.selected');
		if (!$selectedItem.length) return;

		$this = $(this);
		$menuItems = $prev.find('.MenuItem');

		var selPos = 0;
		for (i=0;i<$menuItems.length;i++) {
			$menuItem = $($menuItems[i]);
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

/**
 * Configuración para que los inputs de las grillas envíen al servidor
 * las entradas que se hacen en los campos.
 */
__registerEventListener('elementsUpdated', function() {
	$('.GridRow.Row input').unbind().blur(function() {
		__doAction('send', {
			actionName: 'setValue',
			objectId:   this.id,
			value:      this.value
		});
	});

	$('.GridRow.Row').not('.edit').unbind().click(function() {
		__doAction('send', {
			actionName: 'select',
			objectId:   this.id
		});
	});
});

__registerEventListener('elementsUpdated', function() {
	var to = null;
	$('.DataTable .DataTableHole').bind('scroll', function(a,b,c) {
		var objectId = $(this).closest('.DataTable').attr('id');
		var self = this;
		$(this).parent().find('.DataTableHeaders').css('left',(this.scrollLeft*-1)+'px');

		if ($(this).attr('disable-scroll-event')=='yes') {
			return;
		}
		if (to) {
			clearTimeout(to);
		}
		to = setTimeout(function() {
			__doAction('send', {
				actionName: 'scroll',
				objectId:    objectId,
				left:        self.scrollLeft,
				top:         self.scrollTop
			});
		}, 500);
	});
});

__registerEventListener('elementsUpdated', function() {
	var to = null;
	$('.Tree.container').bind('scroll', function(a,b,c) {
		var objectId = $(this).attr('id');
		var self = this;
		// $(this).parent().find('.DataTableHeaders').css('left',(this.scrollLeft*-1)+'px');

		if ($(this).attr('disable-scroll-event')=='yes') {
			return;
		}
		if (to) {
			clearTimeout(to);
		}
		to = setTimeout(function() {
			__doAction('send', {
				actionName: 'scroll',
				objectId:    objectId,
				left:        self.scrollLeft,
				top:         self.scrollTop
			});
		}, 500);
	});
});

__registerEventListener('elementsUpdated', function() {
	return;
	$('.TextFieldControl').bind('change', function() {
		var $this = $(this);
		__doAction('send', {
			actionName: 'setValue',
			objectId: this.id,
			value: $this.val()
		});
	});
});

__registerEventListener('elementsUpdated', function() {
	$('.ToolItem').unbind('click').bind('click', function() {
		var $this = $(this);
		__doAction('send', {
			actionName: 'press',
			objectId: this.id
		});
	});
});

__registerEventListener('elementsUpdated', function() {
	var to = null;
	$('.TextFieldControl.leaveTyping').unbind('keyup').bind('keyup', function(ev) {
		// if (ev.which == 8 || /[a-z0-9\s]/i.test(String.fromCharCode(e.which)))
		if (to) {
			clearTimeout(to);
		}
		var el = this;
		to = setTimeout(function() {
			// console.log('to',ev);
			__doAction('send',{
				actionName:'leaveTyping',
				objectId:el.id, 
				value:el.value,
				ignoreUpdateObject: true
			});
		}, 400);
	});
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