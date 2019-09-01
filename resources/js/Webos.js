var Webos = {};
Webos.endPoint = '';

Webos.parseResponse = function(data) {
	if (typeof(data)!= 'object') {
		alert('Unexpected response:\n' + data);
	}
	
	if (typeof(data.events) != 'undefined') {
		var i, eventName, eventData;
		for (i in data.events) {
			eventName = data.events[i].name;
			eventData = data.events[i].data;

			Webos.trigger(eventName, eventData);
		}
	}
};

Webos.action = function(actionName, objectID, params) {
	var data = {
		actionName: actionName,
		objectID:   objectID,
		params:     params
	};
	$.ajax({
        url:  this.endPoint,
        data: data,
		type: 'post'
	}).done(Webos.parseResponse).fail(function(r) {
		alert('Unexpected response:\n' + r);
	});
}
Webos.trigger = function(eventName, eventData) {
	eventEngine.triggerEvent(eventName, eventData);
}
Webos.bind = function(eventName, eventHandler, persistance) {
	eventEngine.registerEventListener(eventName, eventHandler, persistance);
}

Webos.syncViewportSize = function() {
	$.ajax({
		url: this.endPoint,
		type: 'post',
		data: {
			syncViewportSize: true,
			width:  window.outerWidth,
			height: window.outerHeight
		}
	});
}