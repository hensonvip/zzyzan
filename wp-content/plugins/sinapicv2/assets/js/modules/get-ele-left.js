/**
 * get ele offset left
 */
module.exports = function(e){
	var l = e.offsetLeft,
		c = e.offsetParent;
	while (c !== null){
		l += c.offsetLeft;
		c = c.offsetParent;
	}
	return l;
};