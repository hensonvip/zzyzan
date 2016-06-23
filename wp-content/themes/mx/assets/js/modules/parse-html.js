module.exports = function(s) {
	var t = document.createElement('div');
	t.innerHTML = s;
	return t.firstChild;
};