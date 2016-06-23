/**
 * replace array
 * @param string str The string ready replace
 * @param string find Search string
 * @param string replace Replace string
 */
module.exports = function(str,find,replace){
	var regex;
	for (var i = 0, len = find.length; i < len; i++) {
		regex = new RegExp(find[i], 'g');
		str = str.replace(regex, replace[i]);
	}
	return str;
}