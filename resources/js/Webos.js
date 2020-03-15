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

Webos.ajax = function(data) {
	return $.ajax({
		url: this.endPoint,
		type: 'post',
		data: data
	});
}
Webos.ajaxAction = function(actionName, objectID, params) {	
	return Webos.ajax({
		actionName: actionName,
		objectID:   objectID,
		params:     params
	});
}
Webos.action = function(actionName, objectID, params) {
	return Webos.ajax({
		actionName: actionName,
		objectID:   objectID,
		params:     params
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
	this.ajax({
		syncViewportSize: true,
		width:  window.outerWidth,
		height: window.outerHeight
	});
}

Webos.blockedKeyEscape = false;
Webos.keyEscape = function() {
	if (Webos.blockedKeyEscape) {
		return;
	}
	Webos.blockedKeyEscape = true;
	this.ajax({
		keyEscape: true,
	}).always(function() {
		Webos.blockedKeyEscape = false;
	});
}