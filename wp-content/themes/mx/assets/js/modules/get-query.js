module.exports = function(){
	var query = {};
	document.location.search.replace(/\??(?:([^=]+)=([^&]*)&?)/g, function () {
		function decode(s) {
			return decodeURIComponent(s.split("+").join(" "));
		}
		query[decode(arguments[1])] = decode(arguments[2]);
	});
	return query;
}