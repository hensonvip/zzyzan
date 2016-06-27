/**
 * get ele offset top
 */
module.exports = function(e){
	var l = e.offsetTop,
		c = e.offsetParent;
	while (c !== null){
		l += c.offsetTop;
		c = c.offsetParent;
	}
	return l;
};