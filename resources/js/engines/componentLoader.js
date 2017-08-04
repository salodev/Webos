/**
 * Component Loader Script.
 *
 * This script extends document DOM element, to provide an easy way to manage
 * loading heavy scripts for special features that you whish add to your site.
 *
 * These scripts will be loaded only if are neccessary. It means if you call
 * any feature, then a function must be called since load script.
 *
 * To do it, you just call your code such as following example:
 *
 * __onLoaded('myWindowHandler', function(){
 *      document.alerWindow('Hello world!');
 * });
 *
 * where myWindowHandler is an identifier for an previously indicated script
 * location.
 *
 * fuction(){} is the code to execute when the script is successfully loaded.
 *
 * To do it right, you must include <script> tag into the head document before
 * others.
 *
 * The location of scripts to load with this feature may be anything.
 */
var cLoader = {};
(function(o){

    o._loadedComponents    = [];
    o._availableComponents = [];
    o._waitingCallback     = [];

    o.onLoaded = function(componentName, callback){
        data = null;
        if (typeof(arguments[2])!='undefined'){
            data = arguments[2];
        }

        if (typeof(this._loadedComponents[componentName])=='undefined'){
            this._addToWaitingCallback(componentName,callback,data);
            this._loadComponent(componentName);
            return;
        }

        (callback)(data);
    }

    o.setAvailableComponent = function(name,location){
        this._availableComponents[name] = location;
    }

    o._addToWaitingCallback = function(componentName,callback,data){
        if (typeof(this._waitingCallback[componentName])=='undefined')
            this._waitingCallback[componentName] = [];

        this._waitingCallback[componentName].push({
            callback: callback,
            data:     data
        });
    }

    o._loadComponent = function(componentName){
        var js;
        var id = 'script-' + componentName;

        (function(d,o){
            if (d.getElementById(id)) {
                return;
            }

            js = d.createElement('script'); js.id = id; js.async = true;
            js.src = o._getComponentLocation(componentName);
            js.onload = function(){
                o._setLoadedComponent(componentName);
                o._executeWaitingCallback(componentName);
            }
            
            d.getElementsByTagName('head')[0].appendChild(js);
        })(document,this);
    }

    o._setLoadedComponent = function(componentName){
        this._loadedComponents[componentName] = componentName;
    }

    o._executeWaitingCallback = function(componentName){
        if (typeof(this._waitingCallback[componentName])=='undefined') return false;
        for (i in this._waitingCallback[componentName]){

            //Callback execution.
            (this._waitingCallback[componentName][i].callback)(this._waitingCallback[componentName][i].data)
        }

        delete this._waitingCallback[componentName];
    }

    o._getComponentLocation = function(componentName){

        if (typeof(this._availableComponents[componentName])=='undefined') return null
        
        return this._availableComponents[componentName];
    }

})(cLoader);

/**
 * Alias method.
 */
function __onLoaded(componentName, callback, data){
    cLoader.onLoaded(componentName, callback, data);
}