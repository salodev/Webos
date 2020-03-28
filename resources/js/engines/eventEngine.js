/**
 * Events Engine Script.
 */
var eventEngine = {};
(function(o){
    o.eventListeners = [];

    o.bind = function(eventName, eventHandler) {
        var persistentEvent = true;

        if (typeof(arguments[2])!='undefined') {
            persistentEvent = arguments[2];
        }

        if (typeof(this.eventListeners[eventName])=='undefined') {
            this.eventListeners[eventName] = [];
        }

        this.eventListeners[eventName].push({
            eventHandler:eventHandler,
            persistent:persistentEvent
        });
    }

    o.trigger = function(eventName) {
        var eventData = null;

        if (typeof(arguments[1])!='undefined') eventData = arguments[1];

        var nonPersistents = []

        for (order in this.eventListeners[eventName]) {
            var tHandler = this.eventListeners[eventName][order];
            new tHandler.eventHandler(eventData);

            if (!tHandler.persistent) nonPersistents.push(order);
        }

        // Non persistent event handlers must be destroyed.
        for (i in nonPersistents) {
            delete this.eventListeners[eventName][nonPersistents[i]];
        }
    }

    o.remove = function(eventName) {
        if (typeof(this.eventListeners[eventName])!='undefined') {
            delete this.eventListeners[eventName];
        }
    }

})(eventEngine);
