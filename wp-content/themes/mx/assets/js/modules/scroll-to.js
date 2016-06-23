module.exports = function(top, callback){
	// ease in out function thanks to:
	// http://blog.greweb.fr/2012/02/bezier-curve-based-easing-functions-from-concept-to-implementation/
	var easeInOutCubic = function (t) { return t<.5 ? 4*t*t*t : (t-1)*(2*t-2)*(2*t-2)+1 }
	var position = function(start, end, elapsed, duration) {
	    if (elapsed > duration) return end;
	    return start + (end - start) * easeInOutCubic(elapsed / duration);
	}

	var smoothScroll = function(el, duration, callback, context){
	    duration = duration || 500;
	    context = context || window;
	    var start = window.pageYOffset;

	    var end = parseInt(top),
	    clock = (+new Date),
	    requestAnimationFrame = window.requestAnimationFrame ||
	        window.mozRequestAnimationFrame || window.webkitRequestAnimationFrame ||
	        function(fn){
		        window.setTimeout(fn, 15);
	        };

	    var step = function(){
	        var elapsed = (+new Date) - clock;
	        if (context !== window) {
	        	context.scrollTop = position(start, end, elapsed, duration);
	        }
	        else {
	        	window.scroll(0, position(start, end, elapsed, duration));
	        }

	        if (elapsed > duration) {
	            if (typeof callback === 'function') {
	                callback(el);
	            }
	        } else {
	            requestAnimationFrame(step);
	        }
	    }
	    step();
    };
    smoothScroll(top);
};