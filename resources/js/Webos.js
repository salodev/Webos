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

Webos.ajax = function(uri, data) {
	return $.ajax({
		url: this.endPoint + uri,
		type: 'post',
		data: data
	});
}
Webos.action = function(actionName, objectID, params, ignoreUpdateObject) {
	return Webos.ajax('action', {
		actionName:         actionName,
		objectID:           objectID,
		params:             params,
		ignoreUpdateObject: ignoreUpdateObject||false
	}).done(Webos.parseResponse).fail(function(r) {
		alert('Unexpected response:\n' + r);
	});
}

Webos.syncViewportSize = function() {
	this.ajax('syncViewportSize', {
		width:  window.outerWidth,
		height: window.outerHeight
	});
}

Webos.blockedKeyEscape = false;
Webos.keyEscape = function() {
	if (Webos.blockedKeyEscape) {
		return;
	}
	return; // now blocked.
	Webos.blockedKeyEscape = true;
	this.ajax('keyEscape').always(function() {
		Webos.blockedKeyEscape = false;
	});
}

Webos.trigger = function(eventName, eventData) {
	eventEngine.triggerEvent(eventName, eventData);
}

Webos.bind = function(eventName, eventHandler, persistance) {
	eventEngine.registerEventListener(eventName, eventHandler, persistance);
}