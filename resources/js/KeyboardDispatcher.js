var KeyboardDispatcher = {};

KeyboardDispatcher.directives = [];

KeyboardDispatcher.start = function(ev) {
	var self = this;
	$(document).keydown(function(ev) {
		self.dispatch(ev.key, ev.keyCode);
	});
}


KeyboardDispatcher.dispatch = function(key, keyCode) {
	var self = this;
	
	//@todo: could has cache for this selection
	$('[webos][container-key-receiver]').last().each(function() {
		
		/**
		 * For any purpose, dispatch this event.
		 */
		self.dispatchEvent(this, 'key-press', key, keyCode);

		/**
		 * All meaning typing a text into a textbox
		 * will be dispatch as it.
		 */
		if (
			keyCode ===  8 || // Backspace
			keyCode === 46 || // Delete
			(keyCode >= 48 && keyCode <= 57 ) || // Numbers
			(keyCode >= 96 && keyCode <= 111) || // Numbers (Numpad)
			(keyCode >= 65 && keyCode <= 90 ) // Letters
		) {
			self.dispatchEvent(this, 'key-press-type', key, keyCode);
		}
		
		/**
		 * Key enter
		 */
		if (keyCode === 13) {
			self.dispatchEvent(this, 'key-press-enter', key, keyCode);
		}
		
		/**
		 * Key escape
		 */
		if (keyCode === 27) {
			self.dispatchEvent(this, 'key-press-escape', key, keyCode);
		}
		
		/**
		 * Pagination group keys
		 */
		if (keyCode >= 33 && keyCode <= 36) {
			self.dispatchEvent(this, 'key-press-page', key, keyCode);
			if (keyCode === 33) { self.dispatchEvent(this, 'key-press-page-up'      , key, keyCode); }
			if (keyCode === 34) { self.dispatchEvent(this, 'key-press-page-down'    , key, keyCode); }
			if (keyCode === 35) { self.dispatchEvent(this, 'key-press-page-end'     , key, keyCode); }
			if (keyCode === 36) { self.dispatchEvent(this, 'key-press-page-home'    , key, keyCode); }
		}
		
		/**
		 * Middle arrows group keys
		 */
		if (keyCode >= 37 && keyCode <= 40) {
			self.dispatchEvent(this, 'key-press-arrow', key, keyCode);
			if (keyCode === 37) { self.dispatchEvent(this, 'key-press-arrow-left'   , key, keyCode); }
			if (keyCode === 38) { self.dispatchEvent(this, 'key-press-arrow-up'     , key, keyCode); }
			if (keyCode === 39) { self.dispatchEvent(this, 'key-press-arrow-right'  , key, keyCode); }
			if (keyCode === 40) { self.dispatchEvent(this, 'key-press-arrow-down'   , key, keyCode); }
		}
		
		/**
		 * Function keys
		 */
		if (keyCode >= 112 && keyCode <= 123) {	
			self.dispatchEvent(this, 'key-press-function', key, keyCode);
			if (keyCode === 112) { self.dispatchEvent(this, 'key-press-function-f1' , key, keyCode); }
			if (keyCode === 113) { self.dispatchEvent(this, 'key-press-function-f2' , key, keyCode); }
			if (keyCode === 114) { self.dispatchEvent(this, 'key-press-function-f3' , key, keyCode); }
			if (keyCode === 115) { self.dispatchEvent(this, 'key-press-function-f4' , key, keyCode); }
			if (keyCode === 116) { self.dispatchEvent(this, 'key-press-function-f5' , key, keyCode); }
			if (keyCode === 117) { self.dispatchEvent(this, 'key-press-function-f6' , key, keyCode); }
			if (keyCode === 118) { self.dispatchEvent(this, 'key-press-function-f7' , key, keyCode); }
			if (keyCode === 119) { self.dispatchEvent(this, 'key-press-function-f8' , key, keyCode); }
			if (keyCode === 120) { self.dispatchEvent(this, 'key-press-function-f9' , key, keyCode); }
			if (keyCode === 121) { self.dispatchEvent(this, 'key-press-function-f10', key, keyCode); }
			if (keyCode === 122) { self.dispatchEvent(this, 'key-press-function-f11', key, keyCode); }
			if (keyCode === 123) { self.dispatchEvent(this, 'key-press-function-f12', key, keyCode); }
		}
		
	});
};

/**
 * Because is directive-based, any other named distinct event can be
 * registered here
 */
KeyboardDispatcher.addDirective = function(eventName, directiveName) {
	if (typeof(this.directives[eventName]) === 'undefined') {
		this.directives[eventName] = [];
	}
	
	this.directives[eventName].push(directiveName);
}

/**
 * It deals with specific implementation.
 * Container may conten keyboard directives, so send first
 * Next, for all child nodes will sent.
 */
KeyboardDispatcher.dispatchEvent = function(container, eventName, key, keyCode) {
	// console.log('enviando', eventName, 'a', Directives.getObjectId(container), key, keyCode, container);
	$(container).trigger(eventName, {
		key:     key,
		keyCode: keyCode
	});
	
	var directives = [eventName].concat(this.directives[eventName]||[]);
	
	for(var i in directives) {
		directives[i] = '[webos][' + directives[i] + ']';
	}
	var selector = directives.join();
	$(container).find(selector).each(function() {
		// console.log('enviando', eventName, 'a', $(this).attr('id'), key, keyCode);
		$(this).trigger(eventName, {
			key:     key,
			keyCode: keyCode
		});
	});
}

KeyboardDispatcher.start();
