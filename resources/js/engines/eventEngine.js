/**
 * Events Engine Script.
 */
var eventEngine = {};
(function(o){
    o.eventListeners = [];

    o.registerEventListener = function(eventName, eventHandler){
        var persistentEvent = true;

        if (typeof(arguments[2])!='undefined'){
            persistentEvent = arguments[2];
        }

        if (typeof(this.eventListeners[eventName])=='undefined'){
            this.eventListeners[eventName] = [];
        }

        this.eventListeners[eventName].push({
            eventHandler:eventHandler,
            persistent:persistentEvent
        });
    }

    o.triggerEvent = function(eventName){
        var eventData = null;

        if (typeof(arguments[1])!='undefined') eventData = arguments[1];

        var nonPersistents = []

        for (order in this.eventListeners[eventName]){
            var tHandler = this.eventListeners[eventName][order];
            new tHandler.eventHandler(eventData);

            if (!tHandler.persistent) nonPersistents.push(order);
        }

        // Non persistent event handlers must be destroyed.
        for (i in nonPersistents){
            delete this.eventListeners[eventName][nonPersistents[i]];
        }
    }

    o.removeEvent = function(eventName){
        if (typeof(this.eventListeners[eventName])!='undefined'){
            delete this.eventListeners[eventName];
        }
    }

})(eventEngine);

function __triggerEvent(eventName, eventData){
    eventEngine.triggerEvent(eventName, eventData);
}

function __registerEventListener (eventName, eventHandler, persistance){
    eventEngine.registerEventListener(eventName, eventHandler, persistance);
}