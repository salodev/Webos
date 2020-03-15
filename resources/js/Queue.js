var Queue = {};
function Task(fn, name) {
	this.fn   = fn || function() {};
	this.name = name;
}

Queue.list = [];
Queue.waiting = false;
Queue.riding = null;

Queue.action = function(fn, name) {
	var def  = new $.Deferred;
	var prom = def.promise();
	var task = new Task(fn, name);
	prom.task = task;
	this.list.push({
		task: task,
		deferred: def
	});
	this.check();
	return prom;
}

Queue.check = function() {
	var q = this;
	if (!this.waiting) {
		var next = this.list.shift();
		if (next == undefined) {
			return;
		}
		this.waiting = true;
		this.riding = next;
		next.task.fn().done(function(d) {
			next.deferred.resolve(d);
		}).fail(function(d) {
			next.deferred.reject(d);
		}).always(function() {
			q.riding = null;
			q.waiting = false;
			q.check();
		});
	}
}

Queue.cancel = function(task) {
	if (this.riding === task) {
		console.log('cancelling');
		this.riding = null;
		this.waiting = false;
	}
	for(var i in this.list) {
		if (this.list[i].task === task) {
			console.log('cancelling');
			this.list.splice(i, 1);
			if (this.riding === task) {
				this.riding = null;
			}
			this.waiting = false;
			return;
		}
	}
}

Queue.timeout = function(fn, time) {
	var to = null;
	def = Queue.action(function() {
		to = setTimeout(fn, time);
	});
	def.cancel = function() {
		clearTimeout(to);
		Queue.remove(def.task);
	}
	return def;
}