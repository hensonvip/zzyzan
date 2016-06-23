/**
 * in_screen
 *
 * @return bool
 * @link https://msdn.microsoft.com/en-us/library/ie/ms534303%28v=vs.85%29.aspx
 */
module.exports = function(o){
	var p = o.offsetParent,
		t = o.offsetTop,
		h = p.clientHeight;
    return t <= h;
};