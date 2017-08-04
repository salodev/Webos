/**
 * Actions Engine Script.
 */
var actionEngine = {};

(function(o){
    o.actionHandlers = [];

    o.registerActionHandler = function(actionName, actionHandler){
        this.actionHandlers[actionName] = actionHandler;
    }

    o.doAction = function(actionName){
        var actionData = null;

        if (typeof(arguments[1])!='undefined') actionData = arguments[1];

        if (typeof(this.actionHandlers[actionName])=='undefined') return false;

        (this.actionHandlers[actionName])(actionData);
    }

    o.removeAction = function(actionName){
        if (typeof(this.actionHandlers[actionName])!='undefined'){
            delete this.actionHandlers[actionName];
        }
    }

})(actionEngine);

function __doAction(actionName, actionParams){
    actionEngine.doAction(actionName, actionParams);
}

function __registerAction(actionName, actionHandler){
    actionEngine.registerActionHandler(actionName, actionHandler);
}