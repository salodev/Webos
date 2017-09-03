var Webos = {};
Webos.endPoint = '/';

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
	}).done(function(data) {
		if (typeof(data.events) != 'undefined') {
			var i, eventName, eventData;
			for (i in data.events) {
				eventName = data.events[i].name;
				eventData = data.events[i].data;

				__triggerEvent(eventName, eventData);
			}
		}
	}).fail(function(r) {
		alert('unexpected response:\n' + r);
	});
}